#!/bin/bash

# Clear Cache Script for Laravel Application
# Use this script after minor code changes that require cache refresh

echo "========================================="
echo "Starting Cache Clear for All Containers"
echo "========================================="
echo ""

# Array of container names
CONTAINERS=("cosmic-app-1-prod" "cosmic-app-2-prod" "cosmic-app-3-prod")

for CONTAINER in "${CONTAINERS[@]}"; do
    echo "📦 Processing: $CONTAINER"
    echo "-----------------------------------"
    
    # Clear application cache
    echo "  → Clearing application cache..."
    docker exec $CONTAINER php artisan cache:clear
    
    # Clear config cache
    echo "  → Clearing config cache..."
    docker exec $CONTAINER php artisan config:clear
    
    # Clear route cache
    echo "  → Clearing route cache..."
    docker exec $CONTAINER php artisan route:clear
    
    # Clear compiled views
    echo "  → Clearing compiled views..."
    docker exec $CONTAINER php artisan view:clear
    
    # Clear compiled classes
    echo "  → Clearing compiled classes..."
    docker exec $CONTAINER php artisan clear-compiled
    
    # Clear event cache
    echo "  → Clearing event cache..."
    docker exec $CONTAINER php artisan event:clear 2>/dev/null || echo "  ℹ️  Event cache clear skipped (not available)"
    
    # Clear Filament cache (if exists)
    echo "  → Clearing Filament cache..."
    docker exec $CONTAINER php artisan filament:cache-components 2>/dev/null || echo "  ℹ️  Filament cache skipped"
    
    # Optimize autoloader
    echo "  → Optimizing autoloader..."
    docker exec $CONTAINER composer dump-autoload --optimize 2>/dev/null || echo "  ℹ️  Composer optimize skipped"
    
    # Reload PHP-FPM
    echo "  → Reloading PHP-FPM..."
    docker exec $CONTAINER bash -c "pkill -USR2 php-fpm" 2>/dev/null || echo "  ℹ️  PHP-FPM reload skipped"
    
    echo "  ✅ $CONTAINER completed"
    echo ""
done

echo "========================================="
echo "Cache Clear Completed for All Containers"
echo "========================================="
echo ""
echo "Summary:"
echo "  • Application cache cleared"
echo "  • Config cache cleared"
echo "  • Route cache cleared"
echo "  • View cache cleared"
echo "  • Compiled classes cleared"
echo "  • PHP-FPM reloaded"
echo ""
echo "Your application is now ready with fresh cache! 🚀"
