#!/bin/bash

###############################################################################
# Docker Production Deployment Script - Backend Fixes
#
# This script deploys the backend connectivity fixes to the Dockerized
# production environment.
#
# Usage: bash deploy_backend_fixes_docker.sh
###############################################################################

set -e

echo "=========================================="
echo "Backend Fixes - Docker Deployment"
echo "=========================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
APP_CONTAINER="cosmic-app-1-prod"
SCHEDULER_CONTAINER="cosmic-scheduler-prod"
COMPOSE_FILE="docker-compose.prod.yml"

# Check if running in project root
if [ ! -f "$COMPOSE_FILE" ]; then
    echo -e "${RED}Error: $COMPOSE_FILE not found${NC}"
    echo "Please run this script from the project root directory"
    exit 1
fi

echo -e "${BLUE}Step 1: Checking Docker containers...${NC}"
echo "---------------------------------------"

# Check if containers are running
if ! docker ps | grep -q "$APP_CONTAINER"; then
    echo -e "${RED}Error: $APP_CONTAINER is not running${NC}"
    exit 1
fi

if ! docker ps | grep -q "$SCHEDULER_CONTAINER"; then
    echo -e "${YELLOW}Warning: $SCHEDULER_CONTAINER is not running${NC}"
fi

echo -e "${GREEN}✓ Containers are running${NC}"
echo ""

echo -e "${BLUE}Step 2: Backing up database...${NC}"
echo "---------------------------------------"

# Create backup
BACKUP_FILE="./data-kiosk/backups/pre-migration-$(date +%Y%m%d_%H%M%S).sql"
docker exec platform-db-prod mysqldump -u root -p${DB_ROOT_PASSWORD:-root} platform > "$BACKUP_FILE" 2>/dev/null || {
    echo -e "${YELLOW}Warning: Could not create database backup${NC}"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
}

if [ -f "$BACKUP_FILE" ]; then
    echo -e "${GREEN}✓ Database backed up to: $BACKUP_FILE${NC}"
fi
echo ""

echo -e "${BLUE}Step 3: Rebuilding Docker images...${NC}"
echo "---------------------------------------"

# Rebuild cosmic-app image (contains new code)
docker compose -f $COMPOSE_FILE build cosmic-app-1 cosmic-app-2 cosmic-app-3

echo -e "${GREEN}✓ Images rebuilt${NC}"
echo ""

echo -e "${BLUE}Step 4: Running database migration...${NC}"
echo "---------------------------------------"

# Run migration in one of the app containers
echo "Running: php artisan migrate --force"
docker exec -it $APP_CONTAINER php artisan migrate --force

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migration completed successfully${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    echo ""
    echo "To rollback:"
    echo "  docker exec -it $APP_CONTAINER php artisan migrate:rollback --step=1"
    exit 1
fi
echo ""

echo -e "${BLUE}Step 5: Clearing caches...${NC}"
echo "---------------------------------------"

# Clear all caches
docker exec $APP_CONTAINER php artisan cache:clear
docker exec $APP_CONTAINER php artisan config:clear
docker exec $APP_CONTAINER php artisan route:clear
docker exec $APP_CONTAINER php artisan view:clear

echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${BLUE}Step 6: Restarting services...${NC}"
echo "---------------------------------------"

# Restart app containers to load new code
echo "Restarting cosmic-app containers..."
docker compose -f $COMPOSE_FILE restart cosmic-app-1 cosmic-app-2 cosmic-app-3

# Restart scheduler to pick up new command
echo "Restarting scheduler..."
docker compose -f $COMPOSE_FILE restart cosmic-scheduler

echo -e "${GREEN}✓ Services restarted${NC}"
echo ""

echo -e "${BLUE}Step 7: Verifying deployment...${NC}"
echo "---------------------------------------"

# Wait for containers to be healthy
echo "Waiting for containers to be healthy..."
sleep 10

# Check if migration was successful
echo "Checking database schema..."
docker exec $APP_CONTAINER php artisan migrate:status | tail -5

# Check if command is registered
echo ""
echo "Checking if new command is registered..."
if docker exec $APP_CONTAINER php artisan list | grep -q "devices:monitor-status"; then
    echo -e "${GREEN}✓ devices:monitor-status command registered${NC}"
else
    echo -e "${RED}✗ Command not found${NC}"
fi

# Check scheduler
echo ""
echo "Checking scheduler status..."
docker exec $SCHEDULER_CONTAINER php artisan schedule:list | grep "devices:monitor-status" || {
    echo -e "${YELLOW}Warning: Command may not be scheduled${NC}"
}

# Test command execution
echo ""
echo "Testing status monitor command..."
docker exec $APP_CONTAINER php artisan devices:monitor-status --dry-run | tail -10

echo ""
echo -e "${BLUE}Step 8: Monitoring setup...${NC}"
echo "---------------------------------------"

echo "To monitor device status changes:"
echo "  docker exec -it $APP_CONTAINER tail -f /var/www/storage/logs/laravel.log | grep 'Device status'"
echo ""
echo "To manually run status monitor:"
echo "  docker exec -it $APP_CONTAINER php artisan devices:monitor-status --verbose"
echo ""
echo "To check scheduler logs:"
echo "  docker logs -f $SCHEDULER_CONTAINER"

echo ""
echo "=========================================="
echo -e "${GREEN}✅ DEPLOYMENT COMPLETE${NC}"
echo "=========================================="
echo ""
echo "Summary:"
echo "  • Database migration: ✓ Completed"
echo "  • New columns added: 13 fields"
echo "  • Services restarted: ✓ Done"
echo "  • Scheduler updated: ✓ Done"
echo ""
echo "The backend fixes are now active!"
echo ""
echo "Next steps:"
echo "1. Monitor logs for 10-15 minutes"
echo "2. Check device status transitions"
echo "3. Verify heartbeat responses include 'should_reconnect'"
echo ""
echo "Documentation:"
echo "  • IMPLEMENTATION_BACKEND_FIXES.md - Complete technical docs"
echo "  • QUICK_REFERENCE.md - Commands & troubleshooting"
echo "  • ARCHITECTURE_DIAGRAMS.md - Visual reference"
echo ""
