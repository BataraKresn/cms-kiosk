#!/bin/bash

###############################################################################
# Backend Fixes Validation Script
#
# This script validates that all backend fixes have been properly implemented.
# Run after deployment to verify the system is working correctly.
#
# Usage: bash validate_backend_fixes.sh
###############################################################################

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=========================================="
echo "Backend Fixes Validation"
echo "=========================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PASSED=0
FAILED=0
WARNINGS=0

check_passed() {
    echo -e "${GREEN}✓ PASS${NC}: $1"
    ((PASSED++))
}

check_failed() {
    echo -e "${RED}✗ FAIL${NC}: $1"
    ((FAILED++))
}

check_warning() {
    echo -e "${YELLOW}⚠ WARN${NC}: $1"
    ((WARNINGS++))
}

echo "1. Checking file existence..."
echo "----------------------------"

# Check migration
if [ -f "database/migrations/2026_02_02_000001_add_heartbeat_management_fields_to_remotes.php" ]; then
    check_passed "Migration file exists"
else
    check_failed "Migration file missing"
fi

# Check service
if [ -f "app/Services/DeviceHeartbeatService.php" ]; then
    check_passed "DeviceHeartbeatService exists"
else
    check_failed "DeviceHeartbeatService missing"
fi

# Check middleware
if [ -f "app/Http/Middleware/HeartbeatRateLimiter.php" ]; then
    check_passed "HeartbeatRateLimiter middleware exists"
else
    check_failed "HeartbeatRateLimiter middleware missing"
fi

# Check command
if [ -f "app/Console/Commands/DeviceStatusMonitorCommand.php" ]; then
    check_passed "DeviceStatusMonitorCommand exists"
else
    check_failed "DeviceStatusMonitorCommand missing"
fi

# Check admin controller
if [ -f "app/Http/Controllers/Api/Admin/ExternalServiceController.php" ]; then
    check_passed "ExternalServiceController exists"
else
    check_failed "ExternalServiceController missing"
fi

echo ""
echo "2. Checking code modifications..."
echo "----------------------------------"

# Check controller uses service
if grep -q "DeviceHeartbeatService" app/Http/Controllers/Api/DeviceRegistrationController.php; then
    check_passed "Controller uses DeviceHeartbeatService"
else
    check_failed "Controller not using DeviceHeartbeatService"
fi

# Check raw SQL removed
if grep -q "getPdo()->exec" app/Http/Controllers/Api/DeviceRegistrationController.php; then
    check_warning "Raw SQL still present in controller"
else
    check_passed "Raw SQL removed from controller"
fi

# Check middleware registered
if grep -q "heartbeat.rate" app/Http/Kernel.php; then
    check_passed "Middleware registered in Kernel"
else
    check_failed "Middleware not registered"
fi

# Check route uses middleware
if grep -q "middleware('heartbeat.rate')" routes/api.php; then
    check_passed "Middleware applied to heartbeat route"
else
    check_failed "Middleware not applied to route"
fi

# Check scheduled command
if grep -q "devices:monitor-status" app/Console/Kernel.php; then
    check_passed "Command scheduled in Kernel"
else
    check_failed "Command not scheduled"
fi

# Check old timeout removed
if grep -q "auto-disconnect-inactive-devices" app/Console/Kernel.php; then
    check_warning "Old auto-disconnect logic still present"
else
    check_passed "Old auto-disconnect logic removed"
fi

echo ""
echo "3. Checking Laravel environment..."
echo "-----------------------------------"

# Check if artisan is executable
if php artisan --version &>/dev/null; then
    check_passed "Laravel artisan accessible"
else
    check_failed "Cannot execute artisan commands"
fi

# Check if command is registered
if php artisan list | grep -q "devices:monitor-status"; then
    check_passed "devices:monitor-status command registered"
else
    check_failed "Command not registered with artisan"
fi

echo ""
echo "4. Checking database..."
echo "------------------------"

# Check if migration is pending
PENDING_MIGRATIONS=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || echo "0")
if [ "$PENDING_MIGRATIONS" -gt 0 ]; then
    check_warning "$PENDING_MIGRATIONS pending migration(s) - run 'php artisan migrate'"
else
    check_passed "No pending migrations"
fi

# Check if table has new columns (requires DB connection)
if php artisan tinker --execute="echo DB::table('remotes')->whereNotNull('heartbeat_interval_seconds')->count();" &>/dev/null; then
    check_passed "Database schema updated with new columns"
else
    check_warning "New columns may not exist - check migration status"
fi

echo ""
echo "5. Checking cache configuration..."
echo "-----------------------------------"

# Check if cache is working
if php artisan tinker --execute="Cache::put('test', 'value', 60); echo Cache::get('test');" 2>/dev/null | grep -q "value"; then
    check_passed "Cache system functional"
else
    check_warning "Cache system may not be working"
fi

echo ""
echo "6. Checking logging..."
echo "----------------------"

# Check if log directory is writable
if [ -w "storage/logs" ]; then
    check_passed "Log directory writable"
else
    check_failed "Log directory not writable"
fi

# Check if log file exists
if [ -f "storage/logs/laravel.log" ]; then
    check_passed "Laravel log file exists"
else
    check_warning "Laravel log file not yet created"
fi

echo ""
echo "7. Checking documentation..."
echo "-----------------------------"

if [ -f "IMPLEMENTATION_BACKEND_FIXES.md" ]; then
    check_passed "Implementation documentation exists"
else
    check_warning "Implementation documentation missing"
fi

if [ -f "QUICK_REFERENCE.md" ]; then
    check_passed "Quick reference guide exists"
else
    check_warning "Quick reference guide missing"
fi

echo ""
echo "=========================================="
echo "Validation Summary"
echo "=========================================="
echo -e "${GREEN}Passed:${NC}   $PASSED"
echo -e "${YELLOW}Warnings:${NC} $WARNINGS"
echo -e "${RED}Failed:${NC}   $FAILED"
echo ""

if [ $FAILED -gt 0 ]; then
    echo -e "${RED}❌ VALIDATION FAILED${NC}"
    echo "Please fix the failed checks before proceeding."
    exit 1
elif [ $WARNINGS -gt 0 ]; then
    echo -e "${YELLOW}⚠️  VALIDATION PASSED WITH WARNINGS${NC}"
    echo "System should work but please review warnings."
    exit 0
else
    echo -e "${GREEN}✅ ALL CHECKS PASSED${NC}"
    echo "Backend fixes successfully implemented!"
    echo ""
    echo "Next steps:"
    echo "1. Run migration if pending: php artisan migrate"
    echo "2. Test heartbeat: curl -X POST http://localhost/api/devices/heartbeat ..."
    echo "3. Monitor logs: tail -f storage/logs/laravel.log"
    echo "4. Check scheduled task: php artisan devices:monitor-status --dry-run"
    exit 0
fi
