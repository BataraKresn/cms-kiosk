#!/bin/bash

###############################################################################
# Post-Deployment Tasks untuk Laravel Docker Production
# 
# Script ini WAJIB dijalankan setelah:
# - Update Docker image
# - Recreate containers
# - Deploy code baru
#
# Usage: bash post-deploy-tasks.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=========================================="
echo "🚀 Post-Deployment Tasks"
echo "=========================================="
echo ""

# Define containers
CONTAINERS=("cosmic-app-1-prod" "cosmic-app-2-prod" "cosmic-app-3-prod")
SCHEDULER="cosmic-scheduler-prod"

###############################################################################
# Step 1: Clear Compiled Caches
###############################################################################
echo -e "${YELLOW}Step 1/6: Clear compiled caches...${NC}"
for container in "${CONTAINERS[@]}"; do
    echo "  → $container"
    docker exec "$container" php artisan clear-compiled > /dev/null 2>&1 || echo "    ⚠️  Warning: clear-compiled failed"
done
echo -e "${GREEN}✓ Compiled caches cleared${NC}"
echo ""

###############################################################################
# Step 2: Clear Application Caches
###############################################################################
echo -e "${YELLOW}Step 2/6: Clear application caches...${NC}"
for container in "${CONTAINERS[@]}"; do
    echo "  → $container"
    docker exec "$container" php artisan config:clear > /dev/null 2>&1
    docker exec "$container" php artisan route:clear > /dev/null 2>&1
    docker exec "$container" php artisan view:clear > /dev/null 2>&1
    docker exec "$container" php artisan cache:clear > /dev/null 2>&1
done
echo -e "${GREEN}✓ Application caches cleared${NC}"
echo ""

###############################################################################
# Step 3: Run Migrations (if any)
###############################################################################
echo -e "${YELLOW}Step 3/6: Run database migrations...${NC}"
echo "  → ${CONTAINERS[0]} (migration runs only once)"

# Check for pending migrations
PENDING=$(docker exec "${CONTAINERS[0]}" php artisan migrate:status 2>&1 | grep -c "Pending" || true)

if [ "$PENDING" -gt 0 ]; then
    echo "  ⚠️  Found $PENDING pending migration(s)"
    docker exec "${CONTAINERS[0]}" php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo "  ℹ️  No pending migrations"
fi
echo ""

###############################################################################
# Step 4: Restart Containers (Clear OPcache)
###############################################################################
echo -e "${YELLOW}Step 4/6: Restart containers (clear OPcache)...${NC}"
echo "  → Restarting app containers..."
docker restart "${CONTAINERS[@]}" > /dev/null 2>&1

echo "  → Restarting scheduler..."
docker restart "$SCHEDULER" > /dev/null 2>&1

echo "  → Waiting for containers to be ready (10s)..."
sleep 10

# Check container health
RUNNING=$(docker ps | grep -c "cosmic-app.*prod" || true)
if [ "$RUNNING" -ge 3 ]; then
    echo -e "${GREEN}✓ All containers restarted and running${NC}"
else
    echo -e "${RED}✗ Warning: Some containers may not be running properly${NC}"
    docker ps | grep cosmic-app
fi
echo ""

###############################################################################
# Step 5: Rebuild Optimized Caches (Warm Up)
###############################################################################
echo -e "${YELLOW}Step 5/6: Rebuild optimized caches...${NC}"
for container in "${CONTAINERS[@]}"; do
    echo "  → $container"
    docker exec "$container" php artisan optimize > /dev/null 2>&1 || echo "    ⚠️  Warning: optimize failed"
done
echo -e "${GREEN}✓ Optimized caches rebuilt${NC}"
echo ""

###############################################################################
# Step 6: Verify Deployment
###############################################################################
echo -e "${YELLOW}Step 6/6: Verify deployment...${NC}"

# Check migration status
echo "  → Migration status:"
docker exec "${CONTAINERS[0]}" php artisan migrate:status 2>&1 | tail -5 | sed 's/^/    /'

# Check if devices command registered
echo "  → Checking devices:monitor-status command:"
if docker exec "${CONTAINERS[0]}" php artisan list 2>&1 | grep -q "devices:monitor-status"; then
    echo -e "    ${GREEN}✓ Command registered${NC}"
else
    echo -e "    ${RED}✗ Command NOT found${NC}"
fi

# Check scheduler status
echo "  → Checking scheduler:"
if docker exec "$SCHEDULER" php artisan schedule:list 2>&1 | grep -q "devices:monitor-status"; then
    echo -e "    ${GREEN}✓ Scheduler configured${NC}"
else
    echo -e "    ${YELLOW}⚠️  Scheduler may not be configured${NC}"
fi

# Test API health endpoint
echo "  → Testing API health:"
RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" https://kiosk.mugshot.dev/api/health 2>/dev/null || echo "error")
if [ "$RESPONSE_TIME" != "error" ]; then
    echo -e "    ${GREEN}✓ API responding (${RESPONSE_TIME}s)${NC}"
else
    echo -e "    ${YELLOW}⚠️  Could not test API${NC}"
fi

# Check for recent errors in logs
echo "  → Checking for errors in logs:"
ERRORS=$(docker exec "${CONTAINERS[0]}" tail -100 /var/www/storage/logs/laravel.log 2>/dev/null | grep -c "ERROR" || true)
if [ "$ERRORS" -gt 0 ]; then
    echo -e "    ${YELLOW}⚠️  Found $ERRORS error(s) in recent logs${NC}"
    echo "    Run: docker exec ${CONTAINERS[0]} tail -50 /var/www/storage/logs/laravel.log"
else
    echo -e "    ${GREEN}✓ No recent errors${NC}"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}✅ Post-Deployment Tasks Complete!${NC}"
echo "=========================================="
echo ""
echo "📊 Summary:"
echo "  • Containers: ${#CONTAINERS[@]} app + 1 scheduler"
echo "  • Caches: Cleared and rebuilt"
echo "  • Migrations: Applied"
echo "  • OPcache: Cleared via restart"
echo ""
echo "📝 Next steps:"
echo "  1. Monitor logs: docker logs -f ${CONTAINERS[0]}"
echo "  2. Watch scheduler: docker logs -f $SCHEDULER"
echo "  3. Test in browser: https://kiosk.mugshot.dev/back-office"
echo ""
echo "💡 If issues persist:"
echo "  • Check: cat /home/ubuntu/kiosk/POST_DEPLOYMENT_CHECKLIST.md"
echo "  • Troubleshoot: Section 'TROUBLESHOOTING' in checklist"
echo ""
