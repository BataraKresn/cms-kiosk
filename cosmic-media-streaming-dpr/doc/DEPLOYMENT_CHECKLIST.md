# Production Deployment Checklist

## 🚀 Pre-Deployment

### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate `APP_KEY`: `php artisan key:generate`
- [ ] Configure database credentials
- [ ] Set correct `APP_URL`
- [ ] Set `URL_PDF` untuk PDF service
- [ ] Set `SERVICE_REMOTE_DEVICE` untuk device API
- [ ] Configure Redis connection
- [ ] Set `CACHE_DRIVER=redis`
- [ ] Set `QUEUE_CONNECTION=redis`
- [ ] Configure mail settings
- [ ] Set session driver (redis recommended)

### 2. Dependencies
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm install`
- [ ] Run `npm run build`
- [ ] Verify PHP version >= 8.1
- [ ] Verify required PHP extensions installed
- [ ] Install Redis server
- [ ] Install FFmpeg untuk video processing

### 3. Database
- [ ] Create production database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data: `php artisan db:seed --force`
- [ ] Backup existing data (if upgrading)
- [ ] Test database connection

### 4. File Storage
- [ ] Create symbolic link: `php artisan storage:link`
- [ ] Set proper permissions on `storage/`: `chmod -R 775 storage`
- [ ] Set proper permissions on `bootstrap/cache/`: `chmod -R 775 bootstrap/cache`
- [ ] Configure S3/MinIO for production storage (optional)
- [ ] Test file uploads
- [ ] Verify storage disk configuration

---

## 🔧 Server Configuration

### Web Server (Nginx)

**File:** `/etc/nginx/sites-available/cosmic-media`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Increase limits untuk large file uploads
    client_max_body_size 10G;
    client_body_timeout 300s;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/cosmic-media /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### PHP-FPM Configuration

**File:** `/etc/php/8.2/fpm/php.ini`

```ini
upload_max_filesize = 10G
post_max_size = 10G
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
```

**File:** `/etc/php/8.2/fpm/pool.d/www.conf`

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
```

---

## 🔄 Queue & Cache Setup

### Redis Service
- [ ] Install Redis: `sudo apt install redis-server`
- [ ] Start Redis: `sudo systemctl start redis-server`
- [ ] Enable auto-start: `sudo systemctl enable redis-server`
- [ ] Set Redis password (production)
- [ ] Test connection: `redis-cli ping`

### Supervisor (Queue Worker)

**Install:**
```bash
sudo apt install supervisor
```

**Config:** `/etc/supervisor/conf.d/cosmic-media-queue.conf`

```ini
[program:cosmic-media-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
startsecs=0
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cosmic-media-queue:*
```

---

## 🔐 Security

### SSL Certificate (Let's Encrypt)

```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Generate certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### File Permissions

```bash
cd /var/www/html

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Storage and cache
sudo chmod -R 775 storage bootstrap/cache
```

### Firewall

```bash
# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow SSH
sudo ufw allow 22/tcp

# Enable firewall
sudo ufw enable
```

---

## ⚡ Optimization

### Laravel Optimization

```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Optimize composer autoloader
composer dump-autoload --optimize
```

### OPcache Configuration

**File:** `/etc/php/8.2/fpm/conf.d/10-opcache.ini`

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1
```

---

## 📊 Monitoring & Logging

### Log Rotation

**File:** `/etc/logrotate.d/laravel`

```
/var/www/html/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/supervisorctl restart cosmic-media-queue:*
    endscript
}
```

### Health Check Script

**File:** `scripts/health-check.sh`

```bash
#!/bin/bash

# Check if website is responding
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://your-domain.com)
if [ $HTTP_STATUS -ne 200 ]; then
    echo "Website down! Status: $HTTP_STATUS"
    # Send alert
fi

# Check if Redis is running
if ! redis-cli ping > /dev/null 2>&1; then
    echo "Redis is down!"
    # Send alert
fi

# Check if queue workers are running
if ! supervisorctl status cosmic-media-queue:* | grep RUNNING > /dev/null; then
    echo "Queue workers not running!"
    # Send alert
fi
```

**Add to crontab:**
```bash
*/5 * * * * /var/www/html/scripts/health-check.sh >> /var/www/html/storage/logs/health.log 2>&1
```

---

## 🔄 Backup Strategy

### Database Backup

**Script:** `scripts/backup-db.sh`

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"
DB_NAME="cosmic_media_streaming"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u root -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

**Daily backup cron:**
```bash
0 2 * * * /var/www/html/scripts/backup-db.sh
```

### File Backup

```bash
# Backup storage directory
tar -czf /backups/storage_$(date +%Y%m%d).tar.gz storage/app/public

# Backup to S3 (optional)
aws s3 sync storage/app/public s3://your-bucket/backups/storage/
```

---

## 🧪 Testing

### Smoke Tests

```bash
# Test homepage
curl -I https://your-domain.com

# Test API endpoint
curl https://your-domain.com/api/health

# Test display endpoint (replace token)
curl https://your-domain.com/display/test-token

# Test file upload
# (use Postman or similar tool)

# Test queue system
php artisan tinker
>>> App\Jobs\RefreshDisplayJob::dispatch('test-token', env('URL_PDF'));
```

### Performance Testing

```bash
# Install Apache Bench
sudo apt install apache2-utils

# Test performance
ab -n 1000 -c 10 https://your-domain.com/

# Monitor queue processing
watch -n 1 'redis-cli llen queues:default'
```

---

## 📱 Post-Deployment

### Checklist

- [ ] Verify website loads correctly
- [ ] Test user login (Filament admin)
- [ ] Test file upload functionality
- [ ] Verify display endpoint works
- [ ] Check queue workers are processing
- [ ] Test display refresh functionality
- [ ] Verify Redis caching works
- [ ] Check logs for errors
- [ ] Test remote device connectivity
- [ ] Verify PDF generation works
- [ ] Monitor resource usage (CPU, RAM, Disk)
- [ ] Setup monitoring alerts
- [ ] Document any issues
- [ ] Update DNS if needed
- [ ] Inform team deployment complete

### Monitoring Commands

```bash
# Check server resources
htop

# Check disk usage
df -h

# Check queue status
supervisorctl status

# Monitor logs
tail -f storage/logs/laravel.log

# Check failed jobs
php artisan queue:failed

# Monitor Redis
redis-cli info stats
```

---

## 🆘 Rollback Plan

### If Deployment Fails:

1. **Revert Code:**
```bash
git reset --hard HEAD~1
composer install
npm run build
```

2. **Restore Database:**
```bash
mysql -u root -p cosmic_media_streaming < backup_YYYYMMDD_HHMMSS.sql
```

3. **Clear Caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

4. **Restart Services:**
```bash
sudo supervisorctl restart cosmic-media-queue:*
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

---

## 📞 Support Contacts

- **Server Admin:** [Contact info]
- **Database Admin:** [Contact info]
- **Development Team:** [Contact info]
- **Emergency Hotline:** [Phone number]

---

## 📝 Deployment Notes

**Date:** _______________
**Version:** _______________
**Deployed by:** _______________

**Issues encountered:**
_______________________________________
_______________________________________

**Resolution:**
_______________________________________
_______________________________________

**Sign-off:** _______________

---

**Reference:**
- Laravel Deployment: https://laravel.com/docs/10.x/deployment
- Server Requirements: https://laravel.com/docs/10.x/deployment#server-requirements
