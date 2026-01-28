# 🚀 Quick Start Guide - Cosmic Media Streaming Platform

## Microservices Deployment dengan Docker Compose

---

## 📋 Prerequisites

```bash
# Check Docker version
docker --version  # Minimum: 20.10+

# Check Docker Compose version
docker compose version  # Minimum: 2.0+
```

---

## ⚡ Development - Quick Start (5 Menit)

### 1. Setup Environment

```bash
cd /path/to/kiosk

# Copy environment file
cp .env.example .env

# Edit jika perlu (optional untuk dev)
nano .env
```

### 2. Deploy All Services

```bash
# Jalankan SEMUA microservices sekaligus
./deploy-dev.sh
```

**Itu saja!** Script akan otomatis:
- ✅ Build semua Docker images
- ✅ Start MariaDB, Redis, MinIO
- ✅ Import database (platform.sql)
- ✅ Start semua 3 microservices
- ✅ Run migrations
- ✅ Setup Laravel

### 3. Access Services

| Service | URL | Description |
|---------|-----|-------------|
| **Cosmic Media** | http://localhost:8000 | Main CMS Application |
| **Generate PDF** | http://localhost:3333 | PDF Generation Service |
| **Remote Android** | http://localhost:3001 | Device Management |
| **phpMyAdmin** | http://localhost:8080 | Database Management |
| **Redis Commander** | http://localhost:8081 | Redis Management |
| **MinIO Console** | http://localhost:9001 | Object Storage |

**Default Credentials:**
- Database: `platform_user` / `platform_password_dev`
- MinIO: `minioadmin` / `minioadmin123`

---

## 🏭 Production Deployment

### 1. Security Configuration

```bash
cd /path/to/kiosk

# Copy environment file
cp .env.example .env

# Edit dan ganti SEMUA password default!
nano .env
```

**PENTING! Ganti password ini:**
```bash
DB_ROOT_PASSWORD=your_strong_root_password
DB_PASSWORD=your_strong_database_password
MINIO_SECRET=your_strong_minio_password
```

### 2. Deploy to Production

```bash
# Deploy semua services ke production
./deploy-prod.sh
```

Script akan:
- ✅ Security checks
- ✅ Backup database (jika ada)
- ✅ Build production images
- ✅ Deploy semua services
- ✅ Production optimizations

---

## 🔧 Individual Service Deployment

### Deploy Cosmic Media Streaming Saja

```bash
cd cosmic-media-streaming-dpr

# Development
./deploy-dev.sh

# Production
./deploy.sh
```

---

## 📊 Management Commands

### View Logs

```bash
# All services
docker compose -f docker-compose.dev.yml logs -f

# Specific service
docker compose -f docker-compose.dev.yml logs -f cosmic-app
docker compose -f docker-compose.dev.yml logs -f generate-pdf
docker compose -f docker-compose.dev.yml logs -f remote-android
```

### Stop Services

```bash
# Stop all (development)
docker compose -f docker-compose.dev.yml down

# Stop all (production)
docker compose -f docker-compose.prod.yml down

# Stop with volume cleanup
docker compose -f docker-compose.dev.yml down -v
```

### Restart Service

```bash
# Development
docker compose -f docker-compose.dev.yml restart [service-name]

# Production
docker compose -f docker-compose.prod.yml restart [service-name]
```

### Scale Services

```bash
# Scale Laravel app (production)
docker compose -f docker-compose.prod.yml up -d --scale cosmic-app=3

# Scale queue workers
docker compose -f docker-compose.prod.yml up -d --scale cosmic-queue-1=5
```

---

## 🗄️ Database Management

### Backup Database

```bash
# Development
docker compose -f docker-compose.dev.yml exec mariadb \
  mysqldump -uroot -proot_password_dev platform > backup_$(date +%Y%m%d).sql

# Production
docker compose -f docker-compose.prod.yml exec mariadb \
  mysqldump -uroot -p platform > backup_$(date +%Y%m%d).sql
```

### Restore Database

```bash
# Development
docker compose -f docker-compose.dev.yml exec -T mariadb \
  mysql -uroot -proot_password_dev platform < backup.sql

# Production
docker compose -f docker-compose.prod.yml exec -T mariadb \
  mysql -uroot -p platform < backup.sql
```

---

## 🐛 Troubleshooting

### Service Won't Start

```bash
# Check logs
docker compose -f docker-compose.dev.yml logs [service-name]

# Rebuild service
docker compose -f docker-compose.dev.yml build --no-cache [service-name]

# Restart service
docker compose -f docker-compose.dev.yml restart [service-name]
```

### Database Connection Error

```bash
# Check if MariaDB is running
docker compose -f docker-compose.dev.yml ps mariadb

# Check MariaDB logs
docker compose -f docker-compose.dev.yml logs mariadb

# Test connection
docker compose -f docker-compose.dev.yml exec mariadb \
  mysql -uplatform_user -pplatform_password_dev platform
```

### Port Already in Use

```bash
# Find what's using the port
sudo lsof -i :8000

# Stop the process or change port in .env
```

### Clear Everything and Start Fresh

```bash
# WARNING: This will delete all data!
docker compose -f docker-compose.dev.yml down -v
docker system prune -a --volumes

# Then deploy again
./deploy-dev.sh
```

---

## 🔍 Health Checks

### Check All Services

```bash
# Development
docker compose -f docker-compose.dev.yml ps

# Production
docker compose -f docker-compose.prod.yml ps
```

### Test Service Endpoints

```bash
# Cosmic Media
curl http://localhost:8000

# Generate PDF
curl http://localhost:3333

# Remote Android
curl http://localhost:3001

# MariaDB
docker compose -f docker-compose.dev.yml exec mariadb mysqladmin ping

# Redis
docker compose -f docker-compose.dev.yml exec redis redis-cli ping
```

---

## 📚 File Structure Reference

```
kiosk/
├── deploy-dev.sh              ← Run this for dev deployment
├── deploy-prod.sh             ← Run this for prod deployment
├── docker-compose.dev.yml     ← Dev configuration
├── docker-compose.prod.yml    ← Prod configuration
├── .env.example               ← Copy this to .env
├── platform.sql               ← Database dump
├── restore.sql                ← Database restore script
├── README.md                  ← Full documentation
└── QUICK_START.md            ← This file
```

---

## 🎯 Common Workflows

### Daily Development

```bash
# Start services
./deploy-dev.sh

# Code, test, develop...

# View logs if needed
docker compose -f docker-compose.dev.yml logs -f cosmic-app

# Stop when done
docker compose -f docker-compose.dev.yml down
```

### Update Code and Reload

```bash
# Laravel auto-reloads via Vite (no restart needed)

# For other services, restart:
docker compose -f docker-compose.dev.yml restart generate-pdf
docker compose -f docker-compose.dev.yml restart remote-android
```

### Database Changes

```bash
# Run migration
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan migrate

# Rollback
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan migrate:rollback

# Fresh migration (DANGER: drops all tables)
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan migrate:fresh
```

### Clear Caches (Laravel)

```bash
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan config:clear
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan view:clear
```

---

## 💡 Tips & Best Practices

### Development

1. ✅ Gunakan `docker-compose.dev.yml` untuk development
2. ✅ Logs akan real-time dengan hot reload
3. ✅ Database data persisted di volumes
4. ✅ Gunakan phpMyAdmin untuk database management
5. ✅ Gunakan Redis Commander untuk queue monitoring

### Production

1. ✅ Selalu ganti password default
2. ✅ Gunakan `docker-compose.prod.yml`
3. ✅ Setup automated backups
4. ✅ Configure Nginx reverse proxy
5. ✅ Enable SSL/TLS
6. ✅ Setup monitoring (Prometheus + Grafana)
7. ✅ Regular security updates
8. ✅ Document any customizations

### Backup Strategy

```bash
# Daily backup script (add to crontab)
0 2 * * * cd /path/to/kiosk && \
  docker compose -f docker-compose.prod.yml exec -T mariadb \
  mysqldump -uroot -p${DB_ROOT_PASSWORD} platform | \
  gzip > /backups/platform_$(date +\%Y\%m\%d).sql.gz
```

---

## 📞 Need Help?

1. Check [README.md](README.md) for detailed documentation
2. Check [MICROSERVICES_READINESS_ANALYSIS.md](MICROSERVICES_READINESS_ANALYSIS.md) for architecture details
3. Check logs: `docker compose -f docker-compose.dev.yml logs -f [service]`
4. Check service status: `docker compose -f docker-compose.dev.yml ps`

---

## 🎉 Success Checklist

After deployment, verify:

- [ ] All services showing as "Up" in `docker compose ps`
- [ ] Can access Cosmic Media at http://localhost:8000
- [ ] Can access phpMyAdmin at http://localhost:8080
- [ ] Database has tables (check via phpMyAdmin)
- [ ] Laravel logs show no errors
- [ ] Generate PDF service responds to requests
- [ ] Remote Android service responds to requests

**Selamat! Setup microservices berhasil! 🚀**

---

**Last Updated:** January 22, 2026  
**Version:** 1.0.0
