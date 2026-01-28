# Production Deployment Guide

## Production Dockerfile Changes

### ✅ Improvements Made:

1. **Multi-stage Build**
   - Stage 1: Node.js builder untuk compile assets production
   - Stage 2: Production PHP image dengan built assets

2. **Production-Ready Configuration**
   - ❌ Removed: `npm run dev` (development command)
   - ✅ Added: `npm run build` untuk production assets
   - ❌ Removed: `php artisan key:generate` (security risk)
   - ✅ Added: Laravel cache optimization (config, route, view, event)

3. **Security Enhancements**
   - No source code mounting in production
   - Only persistent storage volumes mounted
   - Proper file permissions
   - PHP expose_php disabled

4. **Performance Optimizations**
   - Nginx + PHP-FPM (instead of `php artisan serve`)
   - Gzip compression enabled
   - Static file caching (30 days)
   - PHP-FPM process manager configured
   - Supervisor untuk manage multiple processes

5. **Dependencies**
   - ✅ Added: FFmpeg (untuk video conversion jobs)
   - ✅ Added: Nginx (production web server)
   - ✅ Added: Supervisor (process management)

6. **Proper Port Configuration**
   - ❌ Removed: Port 5173 (Vite dev server)
   - ❌ Removed: Port 8000 (Laravel dev server)
   - ✅ Changed: Port 80 (Nginx production)

7. **Health Check**
   - Custom health check script
   - Checks PHP-FPM, Nginx, dan application status

## Pre-Deployment Checklist

### 1. Environment Variables
Pastikan `.env` production sudah di-set dengan benar:

```bash
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # JANGAN generate ulang!
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cosmic_media_streaming
DB_USERNAME=cosmic_user
DB_PASSWORD=STRONG_PASSWORD_HERE

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

FILESYSTEM_DISK=minio
MINIO_ENDPOINT=http://minio:9000
MINIO_KEY=YOUR_MINIO_ACCESS_KEY
MINIO_SECRET=YOUR_MINIO_SECRET_KEY
MINIO_BUCKET=cosmic-media

# AWS S3 Compatible (MinIO)
AWS_ACCESS_KEY_ID=${MINIO_KEY}
AWS_SECRET_ACCESS_KEY=${MINIO_SECRET}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=${MINIO_BUCKET}
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### 2. Build Production Image

```bash
# Build image
docker-compose -f docker-compose.prod.yml build

# Verify image
docker images | grep cosmic-media
```

### 3. Deploy

```bash
# Start all services
docker-compose -f docker-compose.prod.yml up -d

# Check logs
docker-compose -f docker-compose.prod.yml logs -f app

# Check container health
docker ps
```

### 4. Post-Deployment Tasks

```bash
# Run migrations (only first time or after updates)
docker exec cosmic-media-app php artisan migrate --force

# Create storage link
docker exec cosmic-media-app php artisan storage:link

# Clear all cache (if needed)
docker exec cosmic-media-app php artisan optimize:clear
```

## File Structure Changes

### New Files Created:
- `Dockerfile` - Production Dockerfile (with multi-stage build)
- `Dockerfile.dev` - Development Dockerfile (your original)
- `.dockerignore` - Files to exclude from Docker image
- `docker/nginx/default.conf` - Nginx configuration
- `docker/supervisor/supervisord.conf` - Supervisor configuration
- `docker/healthcheck.sh` - Health check script

### Modified Files:
- `docker-compose.prod.yml` - Updated for production
- `routes/api.php` - Added `/api/health` endpoint

## Development vs Production

### Development (Dockerfile.dev + docker-compose.yml)
```bash
docker-compose up -d
```
- Uses `Dockerfile.dev`
- Mounts source code
- Hot reload enabled
- Port 8000 & 5173 exposed
- Development dependencies included

### Production (Dockerfile + docker-compose.prod.yml)
```bash
docker-compose -f docker-compose.prod.yml up -d
```
- Uses production `Dockerfile`
- No source code mounting
- Assets pre-built
- Port 80 exposed
- Only production dependencies
- Optimized for performance

## Monitoring

### Check Service Status
```bash
# All containers
docker-compose -f docker-compose.prod.yml ps

# Specific service logs
docker-compose -f docker-compose.prod.yml logs -f app
docker-compose -f docker-compose.prod.yml logs -f queue-worker
docker-compose -f docker-compose.prod.yml logs -f scheduler
```

### Health Checks
```bash
# Application health
curl http://localhost/api/health

# Nginx status
docker exec cosmic-media-app ps aux | grep nginx

# PHP-FPM status
docker exec cosmic-media-app ps aux | grep php-fpm

# Queue worker
docker logs cosmic-media-queue
```

## Troubleshooting

### Issue: 502 Bad Gateway
```bash
# Check PHP-FPM
docker exec cosmic-media-app supervisorctl status php-fpm

# Restart PHP-FPM
docker exec cosmic-media-app supervisorctl restart php-fpm
```

### Issue: Permission Denied
```bash
# Fix storage permissions
docker exec cosmic-media-app chown -R www-data:www-data /var/www/storage
docker exec cosmic-media-app chmod -R 775 /var/www/storage
```

### Issue: Queue not processing
```bash
# Check queue worker
docker logs cosmic-media-queue

# Restart queue worker
docker-compose -f docker-compose.prod.yml restart queue-worker
```

## Scaling

### Scale Queue Workers
```bash
docker-compose -f docker-compose.prod.yml up -d --scale queue-worker=3
```

### Scale Application
Use a load balancer (Nginx, HAProxy) in front of multiple app containers.

## Backup

### Database Backup
```bash
docker exec cosmic-media-mysql mysqldump -u cosmic_user -p cosmic_media_streaming > backup_$(date +%Y%m%d).sql
```

### Storage Backup
```bash
docker run --rm -v cosmic-media-streaming-dpr_storage_data:/data -v $(pwd):/backup alpine tar czf /backup/storage_backup_$(date +%Y%m%d).tar.gz /data
```

## Security Recommendations

1. **Change Default Passwords**
   - Database root password
   - Database user password
   - MinIO credentials
   - Redis password (if enabled)

2. **Use HTTPS**
   - Setup SSL certificates
   - Use reverse proxy (Nginx/Caddy) with SSL

3. **Firewall Rules**
   - Only expose necessary ports
   - Use Docker network isolation

4. **Regular Updates**
   - Update base images regularly
   - Keep PHP and dependencies updated
   - Monitor security advisories

5. **Log Management**
   - Setup centralized logging
   - Monitor error logs
   - Set log rotation

## Performance Tuning

### PHP-FPM
Edit `Dockerfile` to adjust:
```
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
```

### Nginx
Edit `docker/nginx/default.conf` for:
- Worker processes
- Worker connections
- Buffer sizes

### MySQL
Edit `docker-compose.prod.yml` for:
- innodb_buffer_pool_size
- max_connections
- query_cache_size

### Redis
Adjust memory limits in docker-compose.prod.yml:
```yaml
command: >
  redis-server
  --maxmemory 1gb
  --maxmemory-policy allkeys-lru
```
