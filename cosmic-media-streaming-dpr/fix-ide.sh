#!/bin/bash

# Script untuk memperbaiki IDE cache dan rebuild autoload
# Run this if you see IDE errors (false positives)

set -e

echo "🔧 Fixing IDE Cache Issues..."
echo ""

# 1. Rebuild Composer autoload
echo "📦 Rebuilding Composer autoload..."
composer dump-autoload -o
echo "✅ Composer autoload rebuilt"
echo ""

# 2. Clear Laravel cache
echo "🧹 Clearing Laravel cache..."
php artisan clear-compiled
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "✅ Laravel cache cleared"
echo ""

# 3. Optimize for production (optional)
read -p "Optimize for production? [y/N]: " optimize
if [ "$optimize" = "y" ] || [ "$optimize" = "Y" ]; then
    echo "⚡ Optimizing..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    echo "✅ Optimized"
    echo ""
fi

# 4. Show final status
echo "=========================================="
echo "✅ Cache rebuild completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Close VS Code"
echo "2. Reopen VS Code"
echo "3. Wait for Intelephense to reindex"
echo ""
echo "Or run in VS Code Command Palette:"
echo "> Intelephense: Index workspace"
echo ""
