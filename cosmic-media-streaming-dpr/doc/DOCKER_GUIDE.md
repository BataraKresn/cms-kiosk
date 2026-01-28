# 🐳 Docker Deployment Guide

## 📋 Overview

Docker Compose stack untuk Cosmic Media Streaming dengan:
- **Laravel App** - Main application (port 8000, 5173)
- **MySQL** - Database (port 3306)
- **Redis** - Cache & Queue (port 6379)
- **MinIO** - Object Storage (port 9000, 9001)
- **Queue Worker** - Background job processing
- **Scheduler** - Cron jobs

---

## 🚀 Quick Start

### 1. Prerequisites

Install Docker Desktop:
- Windows: https://docs.docker.com/desktop/windows/install/
- Mac: https://docs.docker.com/desktop/mac/install/
- Linux: `sudo apt install docker docker-compose`

### 2. Setup Environment

```bash
# Copy environment file
cp .env.docker .env

# Edit .env and configure your settings
nano .env  # or use your favorite editor
```

### 3. Deploy

**Linux/Mac:**
```bash
chmod +x deploy.sh
./deploy.sh
```

**Windows PowerShell:**
```powershell
# Convert line endings if needed
(Get-Content deploy.sh) | Set-Content -Encoding UTF8 deploy.sh

# Run with WSL
wsl bash deploy.sh

# Or run commands manually:
docker-compose build
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan config:cache
```

---

## 🔧 Configuration

### Environment Variables (.env)

#### Database:
```env
DB_DATABASE=cosmic_media_streaming
DB_USERNAME=cosmic_user
DB_PASSWORD=change_this_password
DB_ROOT_PASSWORD=change_this_root_password
```

#### MinIO:
```env
MINIO_KEY=minioadmin
MINIO_SECRET=change_this_secret
MINIO_BUCKET=cosmic-media
MINIO_ENDPOINT=http://minio:9000

# AWS S3 compatibility (auto-configured to use MinIO)
AWS_ACCESS_KEY_ID=${MINIO_KEY}
AWS_SECRET_ACCESS_KEY=${MINIO_SECRET}
AWS_BUCKET=${MINIO_BUCKET}
AWS_ENDPOINT=${MINIO_ENDPOINT}
```

#### Storage:
```env
# Use MinIO for all file storage
FILESYSTEM_DISK=minio
```

---

## 📦 Services

### Application (app)
- **URL:** http://localhost:8000
- **Vite:** http://localhost:5173
- **Description:** Laravel application dengan Filament admin
- **Volumes:** `./:/var/www`

### MySQL (mysql)
- **Port:** 3306
- **Database:** cosmic_media_streaming
- **Username:** cosmic_user (configurable)
- **Root Password:** root_password (configurable)
- **Data:** Persisted in `mysql_data` volume

### Redis (redis)
- **Port:** 6379
- **Description:** Cache & Queue driver
- **Memory:** 512MB (configurable)
- **Data:** Persisted in `redis_data` volume

### MinIO (minio)
- **Console:** http://localhost:9001
- **API:** http://localhost:9000
- **Username:** minioadmin (configurable)
- **Password:** minioadmin (configurable)
- **Bucket:** cosmic-media (auto-created)
- **Data:** Persisted in `minio_data` volume

### Queue Worker (queue-worker)
- **Description:** Processes background jobs
- **Command:** `php artisan queue:work redis --tries=3`
- **Auto-restart:** Yes

### Scheduler (scheduler)
- **Description:** Runs Laravel scheduler every minute
- **Command:** `php artisan schedule:run`

---

## 🎯 Usage

### Access Services

```bash
# Main application
http://localhost:8000

# Admin panel
http://localhost:8000/back-office

# MinIO Console
http://localhost:9001
# Login: minioadmin / minioadmin
```

### Docker Commands

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f queue-worker
docker-compose logs -f minio

# Restart services
docker-compose restart

# Rebuild images
docker-compose build --no-cache
docker-compose up -d

# Access app shell
docker-compose exec app bash

# Access MySQL shell
docker-compose exec mysql mysql -u root -p

# Access Redis CLI
docker-compose exec redis redis-cli
```

### Laravel Commands (Inside Container)

```bash
# Access container
docker-compose exec app bash

# Inside container:
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan config:clear
php artisan optimize
```

---

## 📤 File Upload to MinIO

### Upload Flow:

1. **User uploads file** via Filament/Livewire form
2. **Chunk upload** processes large files in chunks
3. **Validation** checks file type and size
4. **MinIO upload** stores file in appropriate folder:
   - `videos/` - Video files
   - `images/` - Image files
   - `pdfs/` - PDF files
   - `html/` - HTML files
   - `files/` - Other files
5. **Response** returns MinIO URL: `http://minio:9000/cosmic-media/videos/filename.mp4`

### Storage Locations:

| Content Type | MinIO Path | Example |
|-------------|-----------|---------|
| Videos | `videos/` | `videos/sample.mp4` |
| Images | `images/` | `images/photo.jpg` |
| PDFs | `pdfs/` | `pdfs/document.pdf` |
| HTML | `html/` | `html/content.html` |
| Other | `files/` | `files/archive.zip` |

### Access Files:

**Public URL:**
```
http://localhost:9000/cosmic-media/{path}
```

**Temporary URL (from Laravel):**
```php
Storage::disk('minio')->temporaryUrl($path, now()->addMinutes(30));
```

**Browser:**
Navigate to MinIO Console → http://localhost:9001 → Browse bucket

---

## 🔍 Monitoring

### Check Container Status

```bash
docker-compose ps
```

Expected output:
```
NAME                          STATUS
cosmic-media-app              Up
cosmic-media-mysql            Up (healthy)
cosmic-media-redis            Up
cosmic-media-minio            Up (healthy)
cosmic-media-queue            Up
cosmic-media-scheduler        Up
```

### Monitor Logs

```bash
# All services
docker-compose logs -f

# Application only
docker-compose logs -f app

# Queue worker only
docker-compose logs -f queue-worker

# Last 100 lines
docker-compose logs --tail=100 app
```

### Check Queue Jobs

```bash
docker-compose exec app php artisan queue:work --once
docker-compose exec app php artisan queue:failed
```

### Check MinIO Files

```bash
# Access MinIO client
docker-compose exec minio-client mc ls minio/cosmic-media

# Or use console
http://localhost:9001
```

---

## 🐛 Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs app

# Check if port is in use
netstat -an | findstr "8000"  # Windows
lsof -i :8000                  # Mac/Linux

# Rebuild from scratch
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Database connection failed

```bash
# Wait for MySQL to be ready
docker-compose exec mysql mysqladmin ping -h localhost

# Check MySQL logs
docker-compose logs mysql

# Verify credentials in .env
# DB_HOST=mysql (not localhost!)
```

### MinIO not accessible

```bash
# Check MinIO health
curl http://localhost:9000/minio/health/live

# Check MinIO logs
docker-compose logs minio

# Recreate MinIO container
docker-compose stop minio
docker-compose rm minio
docker-compose up -d minio
```

### File upload fails

```bash
# Check MinIO bucket exists
docker-compose exec minio-client mc ls minio/

# Create bucket manually
docker-compose exec minio-client mc mb minio/cosmic-media

# Check Laravel logs
docker-compose logs app | grep "MinIO"

# Verify .env configuration
# FILESYSTEM_DISK=minio
# MINIO_ENDPOINT=http://minio:9000
```

### Queue not processing

```bash
# Check queue worker status
docker-compose ps queue-worker

# View queue worker logs
docker-compose logs queue-worker

# Restart queue worker
docker-compose restart queue-worker

# Check Redis connection
docker-compose exec redis redis-cli ping
```

### Permission errors

```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

---

## 📊 Performance Tuning

### MySQL Optimization

Edit `docker-compose.yml`:
```yaml
mysql:
  command: >
    --max_allowed_packet=1073741824
    --innodb_buffer_pool_size=1G
    --innodb_log_file_size=256M
    --max_connections=200
```

### Redis Memory Limit

```yaml
redis:
  command: redis-server --maxmemory 1gb --maxmemory-policy allkeys-lru
```

### Queue Workers

Scale queue workers:
```bash
docker-compose up -d --scale queue-worker=3
```

Or edit docker-compose.yml:
```yaml
queue-worker:
  deploy:
    replicas: 3
```

---

## 🔄 Backup & Restore

### Backup Database

```bash
# Backup to file
docker-compose exec mysql mysqldump -u root -p cosmic_media_streaming > backup_$(date +%Y%m%d).sql

# Or use container command
docker-compose exec mysql sh -c 'mysqldump -u root -p$MYSQL_ROOT_PASSWORD cosmic_media_streaming' > backup.sql
```

### Restore Database

```bash
# Restore from file
docker-compose exec -T mysql mysql -u root -p cosmic_media_streaming < backup.sql
```

### Backup MinIO Data

```bash
# Backup entire volume
docker run --rm \
  -v cosmic-media-streaming-dpr_minio_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/minio_backup.tar.gz /data

# Or use MinIO client
docker-compose exec minio-client mc mirror minio/cosmic-media ./minio-backup
```

### Restore MinIO Data

```bash
# Restore volume
docker run --rm \
  -v cosmic-media-streaming-dpr_minio_data:/data \
  -v $(pwd):/backup \
  alpine sh -c "cd /data && tar xzf /backup/minio_backup.tar.gz --strip 1"
```

---

## 🔐 Security

### Production Checklist

- [ ] Change all default passwords in .env
- [ ] Use strong APP_KEY (run `php artisan key:generate`)
- [ ] Set APP_DEBUG=false
- [ ] Use HTTPS (setup reverse proxy with Nginx/Traefik)
- [ ] Restrict MinIO access (don't expose port 9000 publicly)
- [ ] Enable Redis password authentication
- [ ] Use Docker secrets for sensitive data
- [ ] Enable MySQL SSL connections
- [ ] Setup firewall rules
- [ ] Regular security updates

### Example: Enable Redis Password

`.env`:
```env
REDIS_PASSWORD=your_strong_password
```

`docker-compose.yml`:
```yaml
redis:
  command: redis-server --requirepass ${REDIS_PASSWORD}
```

---

## 🚀 Production Deployment

### Using Docker Swarm

```bash
# Initialize swarm
docker swarm init

# Deploy stack
docker stack deploy -c docker-compose.yml cosmic-media

# Check services
docker stack services cosmic-media

# Scale services
docker service scale cosmic-media_queue-worker=3

# Update service
docker service update --image myregistry/cosmic-media:latest cosmic-media_app
```

### Using Kubernetes

Convert docker-compose to kubernetes:
```bash
kompose convert -f docker-compose.yml
kubectl apply -f .
```

---

## 📝 Development Tips

### Hot Reload for Vite

Vite dev server runs on port 5173 and watches for file changes.

Access at: `http://localhost:5173`

### Xdebug Setup

Add to `Dockerfile`:
```dockerfile
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
```

### Access MySQL from Host

```bash
# Connection details
Host: localhost
Port: 3306
Username: cosmic_user
Password: (from .env)
Database: cosmic_media_streaming
```

---

## 🆘 Need Help?

- Check logs: `docker-compose logs -f`
- Verify .env configuration
- Ensure all containers are running: `docker-compose ps`
- Check port availability
- Review [PERFORMANCE_FIXES.md](PERFORMANCE_FIXES.md)
- Check Laravel logs: `storage/logs/laravel.log`

---

**Last Updated:** January 2026  
**Docker Compose Version:** 3.8  
**Tested On:** Windows 11, Ubuntu 22.04, macOS
