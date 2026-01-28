# 🐳 Docker Deployment Guide - Ubuntu 22.04

## System Requirements

- **OS**: Ubuntu 22.04 LTS
- **Docker**: Latest version (no `docker-compose`, use `docker compose`)
- **RAM**: Minimum 4GB
- **Disk**: Minimum 20GB free space

---

## Installation Steps

### 1. Install Docker on Ubuntu 22.04

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group (no sudo needed)
sudo usermod -aG docker $USER
newgrp docker

# Verify installation
docker --version        # Should show latest version
docker compose version  # Should show version 2.x (NOT docker-compose)
```

### 2. Clone and Configure

```bash
# Clone repository
git clone <your-repository-url>
cd cosmic-media-streaming-dpr

# Copy environment template
cp .env.docker .env

# Edit configuration
nano .env
```

**Required .env changes:**
```env
APP_KEY=                        # Auto-generated on first deploy
DB_PASSWORD=your_strong_password
DB_ROOT_PASSWORD=your_root_password
MINIO_KEY=your_minio_access_key
MINIO_SECRET=your_minio_secret_key
```

### 3. Initial Deployment

```bash
# Make scripts executable
chmod +x deploy.sh update.sh

# Run initial deployment
./deploy.sh
```

Select option 2 for production mode.

---

## Zero-Downtime Updates

When you have new code (updates), use the `update.sh` script:

```bash
# Pull latest code
git pull origin main

# Run update (NO DOWNTIME)
./update.sh
```

**What update.sh does:**
- Builds new Docker images
- Recreates containers **without stopping** (zero-downtime)
- Runs database migrations
- Clears old cache and rebuilds
- Restarts queue workers

**Important**: Update script does NOT use `docker compose down`!

---

## Docker Compose Version

This project uses **Docker Compose V2** (without hyphen):

✅ **Correct**: `docker compose up -d`  
❌ **Wrong**: `docker-compose up -d`

All scripts use the new command format.

---

## Service Access

| Service | URL/Port | Default Credentials |
|---------|----------|---------------------|
| Application | http://localhost:8000 | - |
| Admin Panel | http://localhost:8000/back-office | From your seeder |
| MinIO Console | http://localhost:9001 | minioadmin / minioadmin |
| MySQL | localhost:3306 | root / (from .env) |
| Redis | localhost:6379 | - |

---

## Common Commands

```bash
# View all logs
docker compose logs -f

# View specific service
docker compose logs -f app
docker compose logs -f queue-worker

# Check container status
docker compose ps

# Restart service
docker compose restart app
docker compose restart queue-worker

# Stop all (maintenance)
docker compose stop

# Start all
docker compose start

# Access app shell
docker compose exec app bash

# Run Laravel commands
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan queue:work --once

# Database access
docker compose exec mysql mysql -u root -p

# Check resource usage
docker stats
```

---

## File Storage (MinIO)

All file uploads automatically go to MinIO object storage:

- **Videos**: `cosmic-media/videos/`
- **Images**: `cosmic-media/images/`
- **PDFs**: `cosmic-media/pdfs/`
- **HTML**: `cosmic-media/html/`

**Access MinIO Console:**
1. Open: http://localhost:9001
2. Login: minioadmin / minioadmin
3. Browse: cosmic-media bucket

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker compose logs <service-name>

# Check all containers
docker compose ps -a

# Restart specific service
docker compose restart <service-name>
```

### Database connection error

```bash
# Wait for MySQL to initialize (takes ~30 seconds)
docker compose logs mysql

# Check if MySQL is healthy
docker compose ps mysql

# Test connection
docker compose exec mysql mysqladmin ping -h localhost
```

### MinIO upload fails

```bash
# Check MinIO is running
docker compose ps minio

# Check logs
docker compose logs minio

# Recreate bucket
docker compose restart minio-client
```

### Queue not processing

```bash
# Check queue worker
docker compose logs queue-worker

# Restart queue worker
docker compose restart queue-worker

# Test queue manually
docker compose exec app php artisan queue:work --once
```

### Port already in use

```bash
# Find process using port
sudo lsof -i :8000

# Kill process
sudo kill -9 <PID>

# Or change port in docker-compose.yml
```

---

## Production Security

### Change Default Passwords

```env
# .env file
DB_ROOT_PASSWORD=use_strong_random_password_here
DB_PASSWORD=another_strong_password
MINIO_KEY=your_custom_minio_key
MINIO_SECRET=minimum_40_character_secret_here
```

### Setup Firewall (UFW)

```bash
# Enable firewall
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Block direct access to internal services
sudo ufw deny 3306/tcp  # MySQL
sudo ufw deny 6379/tcp  # Redis
sudo ufw deny 9000/tcp  # MinIO API
sudo ufw deny 9001/tcp  # MinIO Console

# Check status
sudo ufw status
```

### Setup Reverse Proxy (Nginx)

```bash
# Install Nginx
sudo apt install nginx -y

# Create config
sudo nano /etc/nginx/sites-available/cosmic-media
```

Nginx config:
```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/cosmic-media /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Setup SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is configured automatically
```

---

## Backups

### Database Backup

```bash
# Backup
docker compose exec mysql mysqldump -u root -p cosmic_media_streaming > backup_$(date +%Y%m%d).sql

# Restore
docker compose exec -T mysql mysql -u root -p cosmic_media_streaming < backup_20260119.sql
```

### MinIO Backup

```bash
# Backup MinIO volume
docker run --rm \
  -v cosmic-media-streaming-dpr_minio_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/minio_backup_$(date +%Y%m%d).tar.gz /data

# Restore
docker run --rm \
  -v cosmic-media-streaming-dpr_minio_data:/data \
  -v $(pwd):/backup \
  alpine sh -c "cd /data && tar xzf /backup/minio_backup_20260119.tar.gz --strip 1"
```

### Automated Backup Script

Create `backup.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/backups/cosmic-media"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
docker compose exec -T mysql mysqldump -u root -pYOUR_PASSWORD cosmic_media_streaming | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Add to crontab:
```bash
crontab -e

# Daily backup at 2 AM
0 2 * * * /path/to/backup.sh >> /var/log/cosmic-backup.log 2>&1
```

---

## Monitoring

### Check Container Health

```bash
# Container status
docker compose ps

# Resource usage
docker stats

# Disk usage
docker system df
```

### Log Management

```bash
# Tail logs
docker compose logs -f --tail=100 app

# Save logs to file
docker compose logs --no-color > app_logs_$(date +%Y%m%d).log

# Cleanup old logs (optional)
docker compose logs --tail=1000 app > /dev/null
```

---

## Complete Documentation

- [DOCKER_GUIDE.md](DOCKER_GUIDE.md) - Complete Docker guide (200+ lines)
- [PERFORMANCE_FIXES.md](PERFORMANCE_FIXES.md) - Performance optimizations
- [MINIO_UPLOAD.md](MINIO_UPLOAD.md) - MinIO file upload guide
- [REDIS_QUEUE_SETUP.md](REDIS_QUEUE_SETUP.md) - Queue configuration
- [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) - Production checklist

---

## Architecture Overview

```
                    Internet
                       ↓
        ┌─────────────────────────┐
        │  Nginx (Reverse Proxy)  │
        │  Port 80/443 → 8000     │
        └───────────┬─────────────┘
                    ↓
        ┌───────────────────────────────────┐
        │     Docker Compose Stack          │
        ├───────────────────────────────────┤
        │  App (Laravel)                    │
        │  Queue Worker (Background Jobs)   │
        │  Scheduler (Cron Tasks)           │
        ├───────────────────────────────────┤
        │  MySQL 8.0 (Database)             │
        │  Redis 7 (Cache + Queue)          │
        │  MinIO (Object Storage)           │
        └───────────────────────────────────┘
```

---

## Quick Reference

### First Time Setup
```bash
docker compose build
./deploy.sh  # Select option 2 for production
```

### Update Application
```bash
git pull origin main
./update.sh
```

### Restart Everything
```bash
docker compose restart
```

### Complete Reset (WARNING: Deletes data!)
```bash
docker compose down -v
rm .env
cp .env.docker .env
# Edit .env
./deploy.sh
```

---

**Happy deploying! 🚀**

For issues, check logs: `docker compose logs -f app`
