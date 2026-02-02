#!/bin/bash

###############################################################################
# Pre-Deployment Check for Docker Environment
#
# Verifies that all prerequisites are met before deploying backend fixes
###############################################################################

echo "=========================================="
echo "Pre-Deployment Check - Docker Environment"
echo "=========================================="
echo ""

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

CHECKS_PASSED=0
CHECKS_FAILED=0
CHECKS_WARNING=0

check_pass() {
    echo -e "${GREEN}✓ PASS${NC}: $1"
    ((CHECKS_PASSED++))
}

check_fail() {
    echo -e "${RED}✗ FAIL${NC}: $1"
    ((CHECKS_FAILED++))
}

check_warn() {
    echo -e "${YELLOW}⚠ WARN${NC}: $1"
    ((CHECKS_WARNING++))
}

# 1. Check Docker is running
echo "1. Checking Docker..."
if command -v docker &> /dev/null && docker ps &> /dev/null; then
    check_pass "Docker is running"
else
    check_fail "Docker is not running or accessible"
fi

# 2. Check containers
echo ""
echo "2. Checking containers..."
REQUIRED_CONTAINERS=("cosmic-app-1-prod" "cosmic-scheduler-prod" "platform-db-prod" "platform-redis-prod")

for container in "${REQUIRED_CONTAINERS[@]}"; do
    if docker ps | grep -q "$container"; then
        check_pass "$container is running"
    else
        check_fail "$container is not running"
    fi
done

# 3. Check container health
echo ""
echo "3. Checking container health..."
UNHEALTHY=$(docker ps --filter "health=unhealthy" --format "{{.Names}}" | grep cosmic)
if [ -z "$UNHEALTHY" ]; then
    check_pass "All cosmic containers are healthy"
else
    check_fail "Unhealthy containers: $UNHEALTHY"
fi

# 4. Check database connectivity
echo ""
echo "4. Checking database connectivity..."
if docker exec cosmic-app-1-prod php artisan db:show &> /dev/null; then
    check_pass "Database connection working"
else
    check_fail "Cannot connect to database"
fi

# 5. Check migration files exist
echo ""
echo "5. Checking migration files..."
if [ -f "cosmic-media-streaming-dpr/database/migrations/2026_02_02_000001_add_heartbeat_management_fields_to_remotes.php" ]; then
    check_pass "Migration file exists"
else
    check_fail "Migration file not found"
fi

# 6. Check service files exist
echo ""
echo "6. Checking implementation files..."
FILES=(
    "cosmic-media-streaming-dpr/app/Services/DeviceHeartbeatService.php"
    "cosmic-media-streaming-dpr/app/Http/Middleware/HeartbeatRateLimiter.php"
    "cosmic-media-streaming-dpr/app/Console/Commands/DeviceStatusMonitorCommand.php"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        check_pass "$(basename $file) exists"
    else
        check_fail "$(basename $file) not found"
    fi
done

# 7. Check disk space
echo ""
echo "7. Checking disk space..."
DISK_USAGE=$(df -h . | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -lt 90 ]; then
    check_pass "Disk space OK (${DISK_USAGE}% used)"
else
    check_warn "Disk space high (${DISK_USAGE}% used)"
fi

# 8. Check backup directory
echo ""
echo "8. Checking backup directory..."
if [ -d "data-kiosk/backups" ] && [ -w "data-kiosk/backups" ]; then
    check_pass "Backup directory exists and writable"
else
    check_warn "Backup directory not accessible"
fi

# 9. Check Redis connectivity
echo ""
echo "9. Checking Redis..."
if docker exec platform-redis-prod redis-cli ping &> /dev/null; then
    check_pass "Redis is responding"
else
    check_fail "Redis is not responding"
fi

# 10. Check current pending migrations
echo ""
echo "10. Checking migration status..."
PENDING=$(docker exec cosmic-app-1-prod php artisan migrate:status 2>/dev/null | grep -c "Pending" || echo "0")
if [ "$PENDING" -gt 0 ]; then
    check_warn "$PENDING pending migration(s) detected"
    echo "    Note: This is expected if you haven't run the new migration yet"
else
    check_pass "No other pending migrations"
fi

# Summary
echo ""
echo "=========================================="
echo "Summary"
echo "=========================================="
echo -e "${GREEN}Passed:${NC}   $CHECKS_PASSED"
echo -e "${YELLOW}Warnings:${NC} $CHECKS_WARNING"
echo -e "${RED}Failed:${NC}   $CHECKS_FAILED"
echo ""

if [ $CHECKS_FAILED -gt 0 ]; then
    echo -e "${RED}❌ PRE-DEPLOYMENT CHECK FAILED${NC}"
    echo "Please fix the failed checks before deploying."
    echo ""
    exit 1
elif [ $CHECKS_WARNING -gt 0 ]; then
    echo -e "${YELLOW}⚠️  READY WITH WARNINGS${NC}"
    echo "Review warnings before proceeding with deployment."
    echo ""
    echo "To deploy: bash deploy_backend_fixes_docker.sh"
    exit 0
else
    echo -e "${GREEN}✅ ALL CHECKS PASSED${NC}"
    echo "System is ready for deployment!"
    echo ""
    echo "To deploy: bash deploy_backend_fixes_docker.sh"
    exit 0
fi
