# Cosmic Media Streaming Platform - Microservices Architecture

## 📋 Overview

Platform manajemen konten digital signage dan kiosk yang dibangun dengan arsitektur microservices menggunakan Docker dan Docker Compose. Platform ini menggunakan MinIO sebagai object storage untuk semua media files, dengan load balancing untuk high availability.

### Microservices Components:

1. **Cosmic Media Streaming (Laravel)** - Main CMS & Media Management with Filament 3 admin panel
2. **Generate PDF (Node.js)** - PDF Generation, WebSocket Service & HLS Streaming  
3. **Remote Android Device (Python)** - Device Management, Monitoring & Remote Control
4. **MinIO Object Storage** - S3-compatible storage for all media assets (40GB+)
5. **Redis** - High-performance cache, session store, and queue backend
6. **MariaDB 10.11** - Primary database with production-tuned configuration

## 🏗️ Architecture

### Development Environment
```
┌─────────────────────────────────────────────────┐
│           Shared Infrastructure                  │
│  ┌──────────┐  ┌────────┐  ┌──────────┐        │
│  │ MariaDB  │  │ Redis  │  │  MinIO   │        │
│  │  :3306   │  │ :6379  │  │ :9000/01 │        │
│  └──────────┘  └────────┘  └──────────┘        │
└─────────────────────────────────────────────────┘
                      │
         ┌────────────┼────────────┐
         │            │            │
    ┌────▼────┐  ┌───▼────┐  ┌───▼──────┐
    │ Cosmic  │  │Generate│  │  Remote  │
    │  Media  │  │  PDF   │  │ Android  │
    │  :8000  │  │ :3333  │  │  :3001   │
    └─────────┘  └────────┘  └──────────┘
```

### Production Environment
```
                    ┌──────────┐
                    │  Nginx   │
                    │ :8080/443│
                    │ (Reverse │
                    │  Proxy)  │
                    └────┬─────┘
                         │
         ┌───────────────┼───────────────────┐
         │               │                   │
    ┌────▼─────────┐ ┌──▼────┐     ┌───────▼──────┐
    │Cosmic Media  │ │Generate│    │   Remote     │
    │ (3 replicas) │ │  PDF   │    │   Android    │
    │ - app-1      │ │:3333   │    │   :3001      │
    │ - app-2      │ │(WebSocket) │ │ (Flask API) │
    │ - app-3      │ └───┬────┘    └───┬──────────┘
    └────┬─────────┘     │              │
         │               │              │
    ┌────▼───────────────▼──────────────▼────────────┐
    │  Queue Workers (8 workers)                     │
    │  - Video Processing (3x)                       │
    │  - Image Processing (3x)                       │
    │  - Default Queue (2x)                          │
    └────┬──────────────────────────────────────────┘
         │
┌────────┴──────────────────────────────────────────┐
│           Shared Infrastructure                    │
│  ┌──────────┐  ┌────────┐  ┌──────────┐          │
│  │ MariaDB  │  │ Redis  │  │  MinIO   │          │
│  │  10.11   │  │   7    │  │ (S3 API) │          │
│  │ (tuned)  │  │ (AOF)  │  │  40GB+   │          │
│  └──────────┘  └────────┘  └──────────┘          │
└──────────────────────────────────────────────────┘
```

## 🚀 Quick Start

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- 4GB RAM minimum (8GB recommended)
- 20GB disk space

### Folder Structure

All data and documentation are organized with **dev/prod separation**:
- **`data-kiosk/dev/`** - Development runtime data (isolated)
- **`data-kiosk/prod/`** - Production runtime data (isolated)
- **`data-kiosk/backups/`** - Database backups
- **`data-kiosk/logs/`** - Application logs
- **`Doc/`** - All documentation files

See [FOLDER_STRUCTURE.md](Doc/FOLDER_STRUCTURE.md) for details.

### Development Environment

```bash
# 1. Clone repository dan masuk ke direktori
cd /home/ubuntu/kiosk

# 2. Setup environment (sudah ada .env.dev dengan safe defaults)
# File sudah ready, tidak perlu edit untuk development

# 3. Deploy semua microservices (development)
./deploy-dev.sh

# Optional flags:
./deploy-dev.sh --force-rebuild  # Rebuild semua tanpa cache
./deploy-dev.sh --stop           # Stop services dulu sebelum deploy
```

**Note:** Development menggunakan `.env.dev` dengan password aman dan data terpisah di `data-kiosk/dev/`.

### Production Environment

```bash
# 1. Copy dan edit production environment
cp .env.example .env.prod
nano .env.prod

# 2. WAJIB: Ganti semua password CHANGE_ME di .env.prod
# - DB_ROOT_PASSWORD
# - DB_PASSWORD
# - MINIO_SECRET

# 3. Deploy production
./deploy-prod.sh

# Optional flags:
./deploy-prod.sh --force-rebuild  # Rebuild semua tanpa cache
./deploy-prod.sh --stop           # Stop services dulu sebelum deploy

# Script akan otomatis:
# - Validate passwords (tidak boleh default)
# - Build images dengan layer caching
# - Smart recreate (hanya update yang berubah)
# - Run database restoration
# - Execute migrations
```

**Note:** 
- Production deployment akan gagal jika password masih `CHANGE_ME`
- Data production terpisah di `data-kiosk/prod/`
- Images diberi tag `:prod` untuk diferensiasi

## 📦 Service Details

### Service #1: Cosmic Media Streaming (Laravel)
- **Port**: 8000 (dev), internal (prod via Nginx)
- **Tech**: Laravel 10, Filament 3, Livewire 3
- **Performance**:
  - Redis cache/sessions/queue (< 5ms response)
  - OPcache enabled (128MB, 10k files)
  - Optimized autoloader (12k+ classes)
  - Vite assets prebuilt (no compile-on-the-fly)
- **Responsibilities**:
  - User authentication & authorization
  - Media management (Video, Image, HLS, HTML, QR)
  - Layout & display management
  - Scheduling system
  - Device management
  - Content playlist management

### Service #2: Generate PDF (Node.js)
- **Port**: 3333/3335 (dev), internal (prod via Nginx)
- **Tech**: Node.js, Express, Puppeteer
- **Responsibilities**:
  - PDF generation from HTML
  - HLS video streaming
  - WebSocket for real-time updates
  - Media conversion

### Service #3: Remote Android Device (Python)
- **Port**: 3001 (dev), internal (prod via Nginx)
- **Tech**: Python, Flask
- **Responsibilities**:
  - Android device monitoring
  - Remote control commands
  - Device status tracking
  - ADB integration

### Infrastructure Services

#### MariaDB 10.11
- **Dev Config**: 512MB buffer pool, 500 connections
- **Prod Config**: 4GB buffer pool, 1000 connections, thread pool
- **Security**: Root hanya dari localhost, health checks enabled
- **Persistence**: Separate dev/prod data directories
- **Backup**: Automated weekly backup with 4-week retention

#### Redis 7-alpine
- **Usage**: Cache (DB 1), Sessions (DB 0), Queue (DB 0)
- **Performance**: < 5ms response time, persistent AOF snapshots
- **Config**: 2GB maxmemory, allkeys-lru eviction policy
- **Persistence**: RDB snapshots (900/1, 300/10, 60/10000)

#### MinIO S3-Compatible Object Storage
- **Usage**: All media files (videos, images, PDFs, HTML)
- **Current Size**: 40GB+ (241 files)
- **API**: S3-compatible REST API
- **Management**: Web console at :9001
- **Bucket**: `cms` bucket with public read access
- **Migration**: Use `migrate-storage-to-minio-improved.sh` for data imports
- **Important**: Always upload via API (mc cp), NOT direct folder copy
- **Performance**: Direct object access, no filesystem overhead

## 🗄️ Database

Database `platform.sql` akan otomatis di-import saat MariaDB pertama kali dijalankan.

### Automatic Restoration

Script `restore.sql` akan dijalankan otomatis setelah `platform.sql` untuk:
- Optimasi tabel
- Setup user permissions
- Validasi struktur database

### Manual Backup & Restore

```bash
# Backup database (automated script)
./backup-database.sh

# Restore database (interactive)
./restore-database.sh

# Setup cronjob untuk backup mingguan
crontab -e
# Tambahkan: 0 2 * * 0 /home/ubuntu/kiosk/backup-database.sh
```

**Backup Features:**
- Gzip compression untuk save space
- Retention: Keep 4 backups terbaru
- Logging ke `data-kiosk/logs/backup.log`
- Safe: Tidak mengganggu running services

## 💾 MinIO Storage Migration

### Migrate Media Files to MinIO

Use the improved migration script to transfer media files from archives to MinIO:

```bash
# Migrate from compressed archive to MinIO (Production)
./migrate-storage-to-minio-improved.sh prod myfolder.tar.zst

# Migrate for Development
./migrate-storage-to-minio-improved.sh dev backup.tar.gz
```

**Features:**
- ✅ **Upload via MinIO API** (proper way, not direct folder copy)
- ✅ **Auto-detect compression** (zst, gz, tar)
- ✅ **Smart path detection** (finds storage/app/public automatically)
- ✅ **Retry mechanism** (3x retry per file)
- ✅ **Throttling** (0.3s delay between files to avoid rate limiting)
- ✅ **Error logging** to migrate-minio.log
- ✅ **Progress tracking** with success/failure counts
- ✅ **Resume capability** (skip extraction if already done)

**Process Flow:**
1. Extract archive to `storage-temp/`
2. Auto-detect storage path
3. Configure MinIO client with credentials from `.env.prod` or `.env.dev`
4. Create/clear MinIO bucket
5. Upload files one-by-one via API with retry
6. Verify upload count and size
7. Optional cleanup of temporary files

**Important:**
- Do NOT manually copy files to `data-kiosk/prod/minio/` folder
- Always use MinIO API (mc cp) for proper metadata indexing
- Files copied directly to volume won't appear in MinIO GUI

## 🔧 Development Commands

### All Services (Main Directory)

```bash
# Deploy all services (smart recreate)
./deploy-dev.sh

# Force rebuild without cache
./deploy-dev.sh --force-rebuild

# Stop all first, then deploy
./deploy-dev.sh --stop

# Stop all services
docker compose -f docker-compose.dev.yml down

# View logs
docker compose -f docker-compose.dev.yml logs -f [service_name]

# Restart service (only changed containers)
docker compose -f docker-compose.dev.yml up -d [service_name]
```

### Cosmic Media Streaming Only

```bash
cd cosmic-media-streaming-dpr

# Deploy dev only
./deploy-dev.sh

# Deploy prod (existing script)
./deploy.sh

# Laravel commands
docker compose -f docker-compose.dev.yml exec app php artisan [command]

# Access container
docker compose -f docker-compose.dev.yml exec app bash
```

## 🌐 Access Points

### Development Mode

| Service | URL | Credentials |
|---------|-----|-------------|
| Cosmic Media | http://localhost:8000 | See app config |
| Generate PDF | http://localhost:3333 | - |
| Remote Android | http://localhost:3001 | - |
| phpMyAdmin | http://localhost:8080 | platform_user / platform_password_dev |
| Redis Commander | http://localhost:8081 | - |
| MinIO Console | http://localhost:9001 | minioadmin / minioadmin123 |

### Production Mode

| Service | URL | Notes |
|---------|-----|-------|
| All Services | http://server:8080 | Via Nginx reverse proxy |
| All Services (HTTPS) | https://server:8443 | Via Nginx reverse proxy |
| MinIO Console | http://server:9001 | Direct access |

**Port Strategy:**
- Only Nginx (8080/8443) exposed to public
- All app services internal (no direct access)
- Infrastructure services (MariaDB, Redis) internal only

## 📁 Project Structure

```
kiosk/
├── README.md                              # Main documentation
├── docker-compose.dev.yml                 # Development orchestration
├── docker-compose.prod.yml                # Production orchestration  
├── deploy-dev.sh                          # Development deploy script
├── deploy-prod.sh                         # Production deploy script
├── backup-database.sh                     # Database backup script
├── restore-database.sh                    # Database restore script
├── migrate-storage-to-minio-improved.sh   # MinIO migration tool
├── .env.dev                               # Development environment
├── .env.prod                              # Production environment
├── .env.example                           # Environment template
│
├── data-kiosk/                    # ⭐ All runtime data
│   ├── dev/                       # Development data (isolated)
│   │   ├── mariadb/               # Dev database files
│   │   ├── redis/                 # Dev cache data
│   │   └── minio/                 # Dev object storage
│   ├── prod/                      # Production data (isolated)
│   │   ├── mariadb/               # Prod database files
│   │   ├── redis/                 # Prod cache data
│   │   └── minio/                 # Prod object storage
│   ├── backups/                   # Database backups (*.sql.gz)
│   ├── logs/                      # Application & backup logs
│   └── nginx/                     # Nginx config & logs
│
├── Doc/                           # ⭐ All documentation
│   ├── QUICK_START.md
│   ├── DEPLOYMENT_CHECKLIST.md
│   ├── FOLDER_STRUCTURE.md
│   └── ...
│
├── cosmic-media-streaming-dpr/    # Service #1: Laravel CMS
│   ├── docker-compose.dev.yml     # Standalone dev config
│   ├── Dockerfile                 # Production build
│   ├── Dockerfile.dev             # Development build
│   └── ...
│
├── generate-pdf/                  # Service #2: PDF Generator
│   ├── Dockerfile
│   └── ...
│
├── remote-android-device/         # Service #3: Device Manager
│   ├── Dockerfile
│   └── ...
│
└── kiosk-touchscreen-dpr-app/     # Android Kiosk App
    └── ...
```

**See also:** [FOLDER_STRUCTURE.md](Doc/FOLDER_STRUCTURE.md) for detailed folder structure.

## 🔒 Security Notes

### Before Production Deployment:

1. ✅ Ganti semua password default
2. ✅ Set `APP_DEBUG=false`
3. ✅ Set `APP_ENV=production`
4. ✅ Generate strong `APP_KEY`
5. ✅ Configure firewall rules
6. ✅ Setup SSL/TLS certificates
7. ✅ Configure backup strategy
8. ✅ Setup monitoring & logging
9. ✅ Review and limit exposed ports
10. ✅ Setup Nginx reverse proxy

## 🐛 Troubleshooting

### Database Connection Issues

```bash
# Check if MariaDB is running
docker compose -f docker-compose.dev.yml ps mariadb

# View MariaDB logs
docker compose -f docker-compose.dev.yml logs mariadb

# Restart MariaDB
docker compose -f docker-compose.dev.yml restart mariadb
```

### Redis Connection Issues

```bash
# Test Redis connection
docker compose -f docker-compose.dev.yml exec redis redis-cli ping

# Clear Redis cache
docker compose -f docker-compose.dev.yml exec redis redis-cli FLUSHALL
```

### MinIO Issues

```bash
# Check MinIO container status
docker compose -f docker-compose.prod.yml ps minio

# View MinIO logs
docker compose -f docker-compose.prod.yml logs minio

# Test MinIO connection via CLI
mc ls local/

# Check bucket contents
mc ls local/cms/

# Get bucket size
mc du local/cms/

# Restart MinIO (if files not visible in GUI after direct copy)
docker compose -f docker-compose.prod.yml restart minio

# REMEMBER: Always upload via API, not direct folder copy!
```

**Common Issues:**
- Files not visible in GUI → You copied directly to volume folder instead of using API
- Upload throttling → Use `migrate-storage-to-minio-improved.sh` with delays
- Connection refused → Check if MinIO container is healthy

### Service Not Starting

```bash
# Check service logs
docker compose -f docker-compose.dev.yml logs -f [service_name]

# Rebuild service
docker compose -f docker-compose.dev.yml build --no-cache [service_name]

# Remove all containers and volumes (CAUTION: data loss)
docker compose -f docker-compose.dev.yml down -v
```

## 📊 Monitoring

### View Real-time Logs

```bash
# All services
docker compose -f docker-compose.dev.yml logs -f

# Specific service
docker compose -f docker-compose.dev.yml logs -f cosmic-app

# Last 100 lines
docker compose -f docker-compose.dev.yml logs --tail=100 cosmic-app
```

### Resource Usage

```bash
# Container stats
docker stats

# Disk usage
docker system df
```

## 🔄 Updates & Maintenance

### Update Services

```bash
# Normal deployment (smart recreate - recommended)
./deploy-dev.sh   # atau ./deploy-prod.sh

# Force rebuild from scratch
./deploy-dev.sh --force-rebuild

# Full restart (stop all first)
./deploy-dev.sh --stop
```

**Smart Deployment:**
- Docker automatically detects changed images
- Only recreates affected containers
- Maintains database and other unchanged services
- Faster and safer than full restart

### Backup Strategy

```bash
# Automated backup (recommended)
./backup-database.sh

# Setup weekly backup via cronjob
crontab -e
# Add: 0 2 * * 0 /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/data-kiosk/logs/backup.log 2>&1

# Manual backup all data
tar -czf kiosk-backup-$(date +%Y%m%d).tar.gz data-kiosk/

# Backup MinIO data
docker compose -f docker-compose.prod.yml exec minio \
  mc mirror /data /backup
```

### Restore Database

```bash
# Interactive restore (recommended)
./restore-database.sh

# Manual restore
gunzip < data-kiosk/backups/platform-YYYYMMDD-HHMMSS.sql.gz | \
  docker compose -f docker-compose.prod.yml exec -T mariadb \
  mysql -uroot -p"${DB_ROOT_PASSWORD}" platform
```

## 📚 Additional Documentation

See [Doc/](Doc/) folder for all documentation:

- [FOLDER_STRUCTURE.md](Doc/FOLDER_STRUCTURE.md) - Folder organization guide
- [QUICK_START.md](Doc/QUICK_START.md) - Quick start guide
- [DEPLOYMENT_CHECKLIST.md](Doc/DEPLOYMENT_CHECKLIST.md) - Deployment checklist
- [FILE_STRUCTURE.md](Doc/FILE_STRUCTURE.md) - Environment files guide
- [CLEANUP_SUMMARY.md](Doc/CLEANUP_SUMMARY.md) - Recent changes
- [MICROSERVICES_READINESS_ANALYSIS.md](Doc/MICROSERVICES_READINESS_ANALYSIS.md) - Architecture analysis
- [MIGRATION_AND_MAINTENANCE_GUIDE.md](Doc/MIGRATION_AND_MAINTENANCE_GUIDE.md) - Migration guide
- [TECHNICAL_DOCUMENTATION.html](Doc/TECHNICAL_DOCUMENTATION.html) - Technical specs

## 🤝 Support

For issues or questions:
1. Check logs: `docker compose logs -f [service]`
2. Review documentation
3. Check Docker and Docker Compose versions
4. Ensure sufficient system resources

## 📄 License

[Your License Here]

---

## 🚀 Recent Improvements (January 2026)

### Storage Migration to MinIO (January 26, 2026)
- ✅ **Complete migration to MinIO S3 storage** (40GB, 241 files)
- ✅ Created `migrate-storage-to-minio-improved.sh` with retry & throttling
- ✅ Automated extraction from compressed archives (zst/gz/tar)
- ✅ Smart path detection for storage/app/public
- ✅ Upload via MinIO API with error handling
- ⚠️ **Important lesson**: Always use MinIO API, never direct folder copy
- 🗑️ Removed obsolete files: migrate-simple.sh, redis-ha-example.yml, redis-sentinel-example.yml

### Performance Optimizations
- ✅ Redis integration (cache/sessions/queue) - 7000ms → < 5ms response
- ✅ MariaDB optimized configs (512MB dev, 4GB prod)
- ✅ Vite assets prebuilt - 32s → < 2s dashboard load
- ✅ OPcache enabled (128MB, 10k files)
- ✅ Autoloader optimized (12k+ classes)

### Infrastructure Improvements  
- ✅ Dev/prod data separation (`data-kiosk/dev/` and `data-kiosk/prod/`)
- ✅ Docker image tagging (`:dev` and `:prod`)
- ✅ Nginx reverse proxy for production
- ✅ Port management (internal services, only Nginx exposed)
- ✅ Docker layer caching (15min → 2min builds)

### Deployment Strategy
- ✅ Smart recreate (no `docker compose down`)
- ✅ Optional `--stop` flag for full restart
- ✅ Optional `--force-rebuild` for clean builds
- ✅ Backup separated from deployment
- ✅ Automated backup script with retention

### Security Enhancements
- ✅ MariaDB root localhost only
- ✅ Health check endpoints
- ✅ Password validation on deploy
- ✅ Secure defaults for development

---

**Last Updated**: January 23, 2026  
**Version**: 2.0.0
