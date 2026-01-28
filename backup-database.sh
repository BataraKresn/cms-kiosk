#!/bin/bash

# Automated Database Backup Script
# Schedule with: crontab -e
# Example: 0 2 * * 0  /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

BACKUP_DIR="data-kiosk/backups"
MAX_BACKUPS=4  # Keep last 4 backups (1 month if weekly)
CONTAINER_NAME="platform-db-prod"
COMPOSE_FILE="docker-compose.prod.yml"

# Load environment variables
if [ -f .env.prod ]; then
    export $(grep -v '^#' .env.prod | xargs)
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"
mkdir -p logs

# Generate backup filename with timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/platform_backup_${TIMESTAMP}.sql"
BACKUP_COMPRESSED="$BACKUP_FILE.gz"

echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Database Backup${NC}"
echo -e "${CYAN}  $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo -e "${RED}❌ Container ${CONTAINER_NAME} is not running${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Container ${CONTAINER_NAME} is running${NC}"
echo ""

# Create backup
echo -e "${YELLOW}📦 Creating database backup...${NC}"
echo -e "${CYAN}   Target: ${BACKUP_FILE}${NC}"

if docker compose -f "$COMPOSE_FILE" exec -T mariadb mysqldump \
    -uroot \
    -p"${DB_ROOT_PASSWORD}" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --quick \
    --lock-tables=false \
    platform > "$BACKUP_FILE" 2>/dev/null; then
    
    echo -e "${GREEN}✅ Backup created successfully${NC}"
    
    # Get backup size
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo -e "${CYAN}   Size: ${BACKUP_SIZE}${NC}"
    
    # Compress backup
    echo ""
    echo -e "${YELLOW}📦 Compressing backup...${NC}"
    gzip -f "$BACKUP_FILE"
    
    COMPRESSED_SIZE=$(du -h "$BACKUP_COMPRESSED" | cut -f1)
    echo -e "${GREEN}✅ Backup compressed${NC}"
    echo -e "${CYAN}   Compressed size: ${COMPRESSED_SIZE}${NC}"
    echo -e "${CYAN}   Location: ${BACKUP_COMPRESSED}${NC}"
    
else
    echo -e "${RED}❌ Backup failed${NC}"
    exit 1
fi

echo ""

# Cleanup old backups
echo -e "${YELLOW}🧹 Cleaning up old backups...${NC}"
echo -e "${CYAN}   Keeping last ${MAX_BACKUPS} backups${NC}"

# Count current backups
BACKUP_COUNT=$(ls -1 "$BACKUP_DIR"/platform_backup_*.sql.gz 2>/dev/null | wc -l)
echo -e "${CYAN}   Current backups: ${BACKUP_COUNT}${NC}"

if [ "$BACKUP_COUNT" -gt "$MAX_BACKUPS" ]; then
    # Remove oldest backups
    REMOVE_COUNT=$((BACKUP_COUNT - MAX_BACKUPS))
    echo -e "${YELLOW}   Removing ${REMOVE_COUNT} old backup(s)...${NC}"
    
    ls -1t "$BACKUP_DIR"/platform_backup_*.sql.gz | tail -n "$REMOVE_COUNT" | while read -r old_backup; do
        echo -e "${CYAN}   - Removing: $(basename "$old_backup")${NC}"
        rm -f "$old_backup"
    done
    
    echo -e "${GREEN}✅ Cleanup completed${NC}"
else
    echo -e "${GREEN}✅ No cleanup needed${NC}"
fi

echo ""

# Summary
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Backup Summary${NC}"
echo -e "${CYAN}========================================${NC}"
echo -e "${GREEN}✅ Database: platform${NC}"
echo -e "${GREEN}✅ Backup file: $(basename "$BACKUP_COMPRESSED")${NC}"
echo -e "${GREEN}✅ Size: ${COMPRESSED_SIZE}${NC}"
echo -e "${GREEN}✅ Total backups: $(ls -1 "$BACKUP_DIR"/platform_backup_*.sql.gz 2>/dev/null | wc -l)${NC}"
echo ""

# Log success
echo "$(date '+%Y-%m-%d %H:%M:%S') - Backup completed: $(basename "$BACKUP_COMPRESSED") (${COMPRESSED_SIZE})" >> logs/backup.log

exit 0
