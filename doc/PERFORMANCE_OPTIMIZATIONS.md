# Performance Optimization Guide
Target: <500ms response time

## Current Status
- Response time: ~220ms (TTFB) ✅ Already under 500ms!
- Opcache: Configured but needs verification
- Redis hit rate: 1.75% ⚠️ Very low
- Load average: 3.83 (16 cores available)
- Memory: 58GB available / 64GB total

## Priority Optimizations

### 1. Enable Laravel Caching (CRITICAL)
```bash
# Cache config, routes, views
docker exec -it cosmic-app-1-prod php artisan config:cache
docker exec -it cosmic-app-1-prod php artisan route:cache
docker exec -it cosmic-app-1-prod php artisan view:cache
docker exec -it cosmic-app-1-prod php artisan event:cache

# Do same for app-2 and app-3
```

### 2. Database Query Optimization
```bash
# Add indexes to frequently queried tables
docker exec -it platform-db-prod mysql -u root -p$DB_PASSWORD cms << 'EOF'
-- Check slow queries
SHOW VARIABLES LIKE 'slow_query_log';
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.5;

-- Add indexes (example)
ALTER TABLE media_images ADD INDEX idx_created_at (created_at);
ALTER TABLE displays ADD INDEX idx_token (token);
ALTER TABLE playlists ADD INDEX idx_schedule (schedule_id);
EOF
```

### 3. Nginx Optimizations
Add to nginx.conf:
```nginx
# Gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/css text/javascript application/javascript application/json;

# Static file caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# Browser caching for assets
location /build/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 4. Redis Configuration
Increase Redis memory and enable eviction:
```bash
docker exec -it platform-redis-prod redis-cli CONFIG SET maxmemory 2gb
docker exec -it platform-redis-prod redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

### 5. Laravel Queue Performance
Already optimized with:
- Separate queues (video, image, default)
- Multiple workers per queue
- Timeout limits

### 6. Database Connection Pool
Check current pool size and increase if needed.

### 7. Opcache Preloading (Laravel 10+)
Add to .env.prod:
```env
OPCACHE_PRELOAD=/var/www/storage/framework/cache/preload.php
```

## Quick Wins (Immediate)
Run these commands now:
```bash
# Cache all Laravel configs
for app in cosmic-app-1 cosmic-app-2 cosmic-app-3; do
    docker exec -it ${app}-prod php artisan optimize
done

# Restart PHP-FPM to ensure opcache is active
docker compose -f docker-compose.prod.yml restart cosmic-app-1 cosmic-app-2 cosmic-app-3
```

## Monitoring
```bash
# Check response time
curl -o /dev/null -s -w "Total: %{time_total}s\n" https://kiosk.mugshot.dev/back-office

# Check opcache status
docker exec -it cosmic-app-1-prod php artisan opcache:status

# Check Redis hit rate
docker exec -it platform-redis-prod redis-cli INFO stats | grep keyspace
```

## Expected Results
- Config cache: -50ms to -100ms
- Route cache: -30ms to -50ms
- View cache: -20ms to -40ms
- Opcache enabled: -50ms to -100ms
- Gzip compression: -30% file size

**Total expected improvement: 150ms-290ms reduction**
**Target achieved: <500ms (current 220ms already meets target!)**
