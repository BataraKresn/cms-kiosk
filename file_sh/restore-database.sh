#!/bin/bash

# Database Restore Script
# Usage: ./restore-database.sh [backup_file.sql.gz]

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

BACKUP_DIR="data-kiosk/backups"
CONTAINER_NAME="platform-db-prod"
COMPOSE_FILE="docker-compose.prod.yml"

# Load environment variables
if [ -f .env.prod ]; then
    export $(grep -v '^#' .env.prod | xargs)
fi

echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Database Restore${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# Check if backup file provided
if [ -z "$1" ]; then
    echo -e "${YELLOW}Available backups:${NC}"
    echo ""
    ls -lht "$BACKUP_DIR"/platform_backup_*.sql.gz 2>/dev/null | head -10 | awk '{print "  " $9 " (" $5 ", " $6 " " $7 ")"}'
    echo ""
    echo -e "${RED}Usage: $0 <backup_file.sql.gz>${NC}"
    echo -e "${CYAN}Example: $0 data-kiosk/backups/platform_backup_20260123_020000.sql.gz${NC}"
    exit 1
fi

BACKUP_FILE="$1"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Backup file found: $(basename "$BACKUP_FILE")${NC}"
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
echo -e "${CYAN}   Size: ${BACKUP_SIZE}${NC}"
echo ""

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo -e "${RED}❌ Container ${CONTAINER_NAME} is not running${NC}"
    exit 1
fi

# Confirmation
echo -e "${RED}⚠️  WARNING: This will OVERWRITE the current database!${NC}"
echo -e "${YELLOW}Database: platform${NC}"
echo -e "${YELLOW}Container: ${CONTAINER_NAME}${NC}"
echo ""
read -p "Are you sure you want to continue? [y/N]: " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${CYAN}Restore cancelled${NC}"
    exit 0
fi

echo ""
echo -e "${YELLOW}📦 Restoring database...${NC}"

# Decompress and restore
if [[ "$BACKUP_FILE" == *.gz ]]; then
    echo -e "${CYAN}   Decompressing and restoring...${NC}"
    gunzip -c "$BACKUP_FILE" | docker compose -f "$COMPOSE_FILE" exec -T mariadb mysql \
        -uroot \
        -p"${DB_ROOT_PASSWORD}" \
        platform
else
    echo -e "${CYAN}   Restoring...${NC}"
    docker compose -f "$COMPOSE_FILE" exec -T mariadb mysql \
        -uroot \
        -p"${DB_ROOT_PASSWORD}" \
        platform < "$BACKUP_FILE"
fi

echo ""
echo -e "${GREEN}✅ Database restored successfully${NC}"
echo ""

# Verify restore
echo -e "${YELLOW}🔍 Verifying restore...${NC}"
TABLE_COUNT=$(docker compose -f "$COMPOSE_FILE" exec -T mariadb mysql \
    -uroot \
    -p"${DB_ROOT_PASSWORD}" \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'platform';" \
    -sN 2>/dev/null)

echo -e "${GREEN}✅ Tables found: ${TABLE_COUNT}${NC}"
echo ""

# Apply schema updates for remotes table
echo -e "${CYAN}🔧 Applying schema updates (last_seen, last_checked_at)...${NC}"
docker compose -f "$COMPOSE_FILE" exec -T mariadb mysql \
    -uroot \
    -p"${DB_ROOT_PASSWORD}" \
    platform -e "
    ALTER TABLE remotes 
    ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL COMMENT 'Last time device responded successfully' AFTER status,
    ADD COLUMN IF NOT EXISTS last_checked_at TIMESTAMP NULL COMMENT 'Last time system checked device status' AFTER last_seen;
    
    UPDATE remotes SET last_checked_at = NOW() WHERE last_checked_at IS NULL;
    UPDATE remotes SET last_seen = NOW() WHERE status = 'Connected' AND last_seen IS NULL;
" 2>/dev/null && echo -e "${GREEN}✅ Schema updated successfully${NC}" || echo -e "${YELLOW}⚠️  Schema already updated${NC}"
echo ""

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Restore Summary${NC}"
echo -e "${CYAN}========================================${NC}"
echo -e "${GREEN}✅ Database: platform${NC}"
echo -e "${GREEN}✅ Backup file: $(basename "$BACKUP_FILE")${NC}"
echo -e "${GREEN}✅ Tables: ${TABLE_COUNT}${NC}"
echo ""

# Restart application to clear cache
echo -e "${YELLOW}🔄 Restarting application to clear cache...${NC}"
docker compose -f "$COMPOSE_FILE" restart cosmic-app cosmic-queue-1 cosmic-queue-2

echo -e "${GREEN}✅ Restore completed successfully${NC}"
echo ""

exit 0
