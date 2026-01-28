# Quick Production Deployment Reference

## 📋 Pre-Deployment Checklist

- [ ] `.env` file configured with production values
- [ ] `APP_KEY` is set (NEVER generate new key!)
- [ ] Database credentials are strong
- [ ] MinIO credentials are changed from defaults
- [ ] All sensitive data secured

## 🚀 Deployment Commands

### First Time Deployment
```bash
# 1. Build production image
docker-compose -f docker-compose.prod.yml build

# 2. Start all services
docker-compose -f docker-compose.prod.yml up -d

# 3. Wait for services to be healthy (30-60 seconds)
docker-compose -f docker-compose.prod.yml ps

# 4. Run database migrations
docker exec cosmic-media-app php artisan migrate --force

# 5. Create storage link
docker exec cosmic-media-app php artisan storage:link

# 6. Verify deployment
curl http://localhost/api/health
```

### Update Deployment
```bash
# 1. Pull latest code
git pull

# 2. Rebuild image
docker-compose -f docker-compose.prod.yml build

# 3. Stop old containers
docker-compose -f docker-compose.prod.yml down

# 4. Start new containers
docker-compose -f docker-compose.prod.yml up -d

# 5. Run migrations if needed
docker exec cosmic-media-app php artisan migrate --force

# 6. Clear cache
docker exec cosmic-media-app php artisan optimize
```

## 🔍 Monitoring Commands

### Check Status
```bash
# All containers
docker-compose -f docker-compose.prod.yml ps

# Health checks
docker ps --format "table {{.Names}}\t{{.Status}}"

# Application health
curl http://localhost/api/health
```

### View Logs
```bash
# All logs
docker-compose -f docker-compose.prod.yml logs -f

# Specific service
docker-compose -f docker-compose.prod.yml logs -f app
docker-compose -f docker-compose.prod.yml logs -f queue-worker
docker-compose -f docker-compose.prod.yml logs -f mysql
```

### Resource Usage
```bash
# All containers
docker stats

# Specific container
docker stats cosmic-media-app
```

## 🛠️ Maintenance Commands

### Restart Services
```bash
# Restart all
docker-compose -f docker-compose.prod.yml restart

# Restart specific service
docker-compose -f docker-compose.prod.yml restart app
docker-compose -f docker-compose.prod.yml restart queue-worker
```

### Clear Cache
```bash
# Application cache
docker exec cosmic-media-app php artisan cache:clear

# Config cache
docker exec cosmic-media-app php artisan config:clear

# All optimizations
docker exec cosmic-media-app php artisan optimize:clear
```

### Database Operations
```bash
# Access MySQL
docker exec -it cosmic-media-mysql mysql -u cosmic_user -p

# Backup database
docker exec cosmic-media-mysql mysqldump -u cosmic_user -p cosmic_media_streaming > backup_$(date +%Y%m%d).sql

# Restore database
docker exec -i cosmic-media-mysql mysql -u cosmic_user -p cosmic_media_streaming < backup.sql
```

### Queue Management
```bash
# View queue status
docker exec cosmic-media-app php artisan queue:work --once

# Restart queue worker
docker-compose -f docker-compose.prod.yml restart queue-worker

# Clear failed jobs
docker exec cosmic-media-app php artisan queue:flush
```

## 🚨 Troubleshooting

### 502 Bad Gateway
```bash
# Check PHP-FPM status
docker exec cosmic-media-app ps aux | grep php-fpm

# Check Nginx status
docker exec cosmic-media-app ps aux | grep nginx

# Restart services
docker-compose -f docker-compose.prod.yml restart app
```

### Permission Issues
```bash
# Fix storage permissions
docker exec cosmic-media-app chown -R www-data:www-data /var/www/storage
docker exec cosmic-media-app chmod -R 775 /var/www/storage
```

### Database Connection Failed
```bash
# Check MySQL status
docker-compose -f docker-compose.prod.yml logs mysql

# Check connection from app
docker exec cosmic-media-app php artisan tinker
>>> DB::connection()->getPdo();
```

### Queue Not Processing
```bash
# Check queue worker logs
docker logs cosmic-media-queue

# Test queue connection
docker exec cosmic-media-app php artisan queue:work --once

# Restart worker
docker-compose -f docker-compose.prod.yml restart queue-worker
```

## 🔒 Security Commands

### Change Database Password
```bash
# 1. Stop app containers
docker-compose -f docker-compose.prod.yml stop app queue-worker scheduler

# 2. Access MySQL
docker exec -it cosmic-media-mysql mysql -u root -p

# 3. In MySQL:
ALTER USER 'cosmic_user'@'%' IDENTIFIED BY 'new_strong_password';
FLUSH PRIVILEGES;
exit;

# 4. Update .env file
# DB_PASSWORD=new_strong_password

# 5. Restart app containers
docker-compose -f docker-compose.prod.yml start app queue-worker scheduler
```

### Rotate MinIO Credentials
```bash
# 1. Stop all services
docker-compose -f docker-compose.prod.yml down

# 2. Update .env
# MINIO_KEY=new_access_key
# MINIO_SECRET=new_secret_key

# 3. Remove old volume (if needed)
docker volume rm cosmic-media-streaming-dpr_minio_data

# 4. Start services
docker-compose -f docker-compose.prod.yml up -d
```

## 📊 Performance Tuning

### Scale Queue Workers
```bash
# Scale to 3 workers
docker-compose -f docker-compose.prod.yml up -d --scale queue-worker=3

# Check workers
docker ps | grep queue
```

### Adjust PHP-FPM Workers
Edit `Dockerfile`:
```dockerfile
pm.max_children = 50        # Increase for more traffic
pm.start_servers = 10       # More initial workers
pm.min_spare_servers = 5
pm.max_spare_servers = 15
```

Then rebuild:
```bash
docker-compose -f docker-compose.prod.yml build app
docker-compose -f docker-compose.prod.yml up -d
```

## 💾 Backup & Restore

### Full Backup
```bash
# Database
docker exec cosmic-media-mysql mysqldump -u cosmic_user -p cosmic_media_streaming > db_backup_$(date +%Y%m%d).sql

# Storage volume
docker run --rm -v cosmic-media-streaming-dpr_storage_data:/data -v $(pwd):/backup alpine tar czf /backup/storage_$(date +%Y%m%d).tar.gz /data

# MinIO volume
docker run --rm -v cosmic-media-streaming-dpr_minio_data:/data -v $(pwd):/backup alpine tar czf /backup/minio_$(date +%Y%m%d).tar.gz /data
```

### Restore
```bash
# Database
docker exec -i cosmic-media-mysql mysql -u cosmic_user -p cosmic_media_streaming < db_backup.sql

# Storage volume
docker run --rm -v cosmic-media-streaming-dpr_storage_data:/data -v $(pwd):/backup alpine sh -c "cd /data && tar xzf /backup/storage_backup.tar.gz --strip 1"
```

## 🔄 Rolling Update Strategy

```bash
# 1. Build new image with tag
docker build -t cosmic-media:v1.1 -f Dockerfile .

# 2. Test new image
docker run -d --name test cosmic-media:v1.1
# ... perform tests ...
docker stop test && docker rm test

# 3. Update docker-compose.prod.yml to use new tag
# image: cosmic-media:v1.1

# 4. Rolling update (one by one)
docker-compose -f docker-compose.prod.yml up -d --no-deps app

# 5. Verify before proceeding
curl http://localhost/api/health

# 6. Update remaining services
docker-compose -f docker-compose.prod.yml up -d
```

## 📞 Support

For detailed documentation:
- [Production Dockerfile Guide](PRODUCTION_DOCKERFILE.md)
- [Dockerfile Changes](DOCKERFILE_CHANGES.md)
- [Docker Guide](DOCKER_GUIDE.md)
