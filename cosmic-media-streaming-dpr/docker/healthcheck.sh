#!/bin/sh

# Check if PHP-FPM is running
if ! pgrep -x php-fpm > /dev/null; then
    echo "PHP-FPM is not running"
    exit 1
fi

# Check if Nginx is running
if ! pgrep -x nginx > /dev/null; then
    echo "Nginx is not running"
    exit 1
fi

# Check if the application responds
if ! curl -f http://localhost/api/health > /dev/null 2>&1; then
    echo "Application health check failed"
    exit 1
fi

echo "All health checks passed"
exit 0
