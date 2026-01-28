# 📁 Structure Guide - File & Folder Organization

**Date:** 29 Januari 2026  
**Platform:** Cosmic Media Streaming - Kiosk Platform  
**Status:** ✅ Organized & Production Ready

---

## 📂 Folder Structure Overview

All data, logs, and runtime files are organized in **`data-kiosk/`** folder.  
All documentation is in **`doc/`** folder.

This ensures clean separation between:
- **Application code** (cosmic-media-streaming-dpr, generate-pdf, remote-android-device, remote-control-relay)
- **Runtime data** (data-kiosk/)
- **Documentation** (doc/)

---

## 🗂️ Complete Structure

```
/home/ubuntu/kiosk/
│
├── README.md                           # Main documentation
├── DEPLOYMENT_GUIDE.md                 # Deployment workflows
├── .env.dev                            # Dev environment config
├── .env.prod                           # Prod environment config
├── docker-compose.dev.yml              # Dev orchestration
├── docker-compose.prod.yml             # Prod orchestration
├── deploy-dev.sh                       # Dev deployment script
├── deploy-prod.sh                      # Prod deployment script
├── backup-database.sh                  # Database backup script
├── restore-database.sh                 # Database restore script
├── platform.sql                        # Database dump
├── restore.sql                         # Database restore script
│
├── data-kiosk/                         # ⭐ All runtime data
│   ├── mariadb/                        # MariaDB data
│   ├── redis/                          # Redis data
│   ├── minio/                          # MinIO object storage
│   ├── minio-backup/                   # MinIO backups
│   ├── backups/                        # Database backups
│   ├── logs/                           # Application logs
│   │   ├── cosmic-app/
│   │   ├── cosmic-queue-1/
│   │   ├── cosmic-queue-2/
│   │   ├── cosmic-scheduler/
│   │   ├── generate-pdf/
│   │   ├── remote-android/
│   │   └── remote-relay/
│   └── nginx/                          # Nginx config & logs
│       ├── nginx.conf
│       ├── ssl/
│       └── logs/
│
├── doc/                                # ⭐ All documentation
│   ├── PROJECT_SUMMARY.md
│   ├── STRUCTURE_GUIDE.md (this file)
│   ├── SERVER_SPECIFICATIONS.md
│   ├── DATABASE_BACKUP_GUIDE.md
│   ├── DEPLOYMENT_CHECKLIST.md
│   ├── QUICK_START.md
│   ├── QUICK_REFERENCE.md
│   ├── ... (27 files total)
│
├── cosmic-media-streaming-dpr/         # Service #1 - Laravel CMS
│   ├── app/
│   ├── config/
│   ├── database/
│   │   └── migrations/
│   │       ├── 2026_01_29_000000_create_remote_control_tables.php
│   │       ├── 2026_01_29_000001_add_device_registration_fields_to_remotes.php
│   │       └── ...
│   ├── docker-compose.dev.yml          # Standalone dev
│   ├── .env                            # Production config
│   ├── .env.dev                        # Dev config
│   └── ...
│
├── generate-pdf/                       # Service #2 - PDF Generation
│   ├── uploads/                        # User uploads
│   ├── hls_output/                     # HLS streaming output
│   ├── Dockerfile
│   ├── index.js
│   └── ...
│
├── remote-control-relay/               # Service #4 - WebSocket Relay
│   ├── server.js                       # Main relay server
│   ├── Dockerfile
│   ├── package.json
│   └── ...
│
├── remote-android-device/              # Service #3 - Device Control
│   ├── Dockerfile
│   ├── app.py
│   └── ...
│
└── kiosk-touchscreen-app/              # Android Kiosk App
    ├── app/src/main/java/com/kiosktouchscreendpr/cosmic/
    │   └── data/
    │       ├── services/
    │       │   ├── ScreenCaptureService.kt
    │       │   ├── InputInjectionService.kt
    │       │   ├── RemoteControlWebSocketClient.kt
    │       │   └── DeviceRegistrationService.kt
    │       └── ...
    └── ...
```

---

## 📄 Docker Compose Files

### ✅ Active Files:

```
/home/ubuntu/kiosk/
├── docker-compose.dev.yml     # Main: All 4 microservices (dev)
├── docker-compose.prod.yml    # Main: All 4 microservices (prod)
│
└── cosmic-media-streaming-dpr/
    └── docker-compose.dev.yml  # Standalone: Cosmic Media only (dev)
```

### Configuration:

**docker-compose.dev.yml:**
- All 4 services: cosmic-app, generate-pdf, remote-android, remote-relay
- Includes: MariaDB, Redis, MinIO, phpMyAdmin, Redis Commander
- Uses bind mounts: `./data-kiosk/`
- Development mode with hot reload

**docker-compose.prod.yml:**
- All 4 services optimized for production
- Includes: Nginx reverse proxy
- Resource limits configured
- Health checks enabled
- Uses bind mounts: `./data-kiosk/`
- Volume mounts for migrations only (performance optimized)

---

## ⚙️ Environment Files (.env)

### Main Directory Environment Files:

```
/home/ubuntu/kiosk/
│
├── .env.example              # Template (reference only)
├── .env.dev                  # ✅ Development (all microservices)
├── .env.prod                 # ✅ Production (all microservices)
```

### Cosmic Media Streaming Environment Files:

```
cosmic-media-streaming-dpr/
├── .env                      # ✅ Production config (ACTIVE)
├── .env.dev                  # ✅ Standalone dev
├── .env.example              # Template
```

### Usage by Scenario:

#### Scenario 1: Deploy All Microservices (Recommended) ⭐

**Development:**
```bash
cd /home/ubuntu/kiosk
./deploy-dev.sh
```
**Uses:** `/home/ubuntu/kiosk/.env.dev`  
**Services:** cosmic-app, cosmic-queue, cosmic-scheduler, generate-pdf, remote-android, remote-relay

**Production:**
```bash
cd /home/ubuntu/kiosk
./deploy-prod.sh --backup
```
**Uses:** `/home/ubuntu/kiosk/.env.prod`  
**Services:** All services + Nginx

---

#### Scenario 2: Deploy Cosmic Media Only (Standalone)

**Development:**
```bash
cd /home/ubuntu/kiosk/cosmic-media-streaming-dpr
./deploy-dev.sh
```
**Uses:** `cosmic-media-streaming-dpr/.env.dev`  
**Services:** app, queue-worker, scheduler (only cosmic media)

**Production:**
```bash
cd /home/ubuntu/kiosk/cosmic-media-streaming-dpr
# Uses existing .env file
```
**Uses:** `cosmic-media-streaming-dpr/.env` ⭐ **Production config**

---

## 💾 Volume Mounts

All Docker volumes use **bind mounts** to `./data-kiosk/`:

| Service | Volume Path | Purpose |
|---------|------------|---------|
| MariaDB | `./data-kiosk/mariadb` | Database files |
| Redis | `./data-kiosk/redis` | Cache & queue data |
| MinIO | `./data-kiosk/minio` | Object storage |
| MinIO Backup | `./data-kiosk/minio-backup` | MinIO backups |
| Database Backups | `./data-kiosk/backups` | Database dumps |
| Nginx Config | `./data-kiosk/nginx/nginx.conf` | Nginx configuration |
| Nginx SSL | `./data-kiosk/nginx/ssl` | SSL certificates |
| Nginx Logs | `./data-kiosk/nginx/logs` | Access & error logs |
| App Logs | `./data-kiosk/logs/*` | Application logs |

### Performance Optimization ⚡

**Volume Mount Strategy (Production):**

✅ **Mounted volumes (OK):**
- `database/migrations/` - For schema updates without rebuild
- `storage/` - Persistent storage
- `logs/` - Application logs

❌ **NOT mounted (Performance):**
- `app/` - Application code (causes 10x slowdown)
- `routes/` - Route definitions (causes 10x slowdown)
- `config/` - Configuration files (causes 10x slowdown)

**Reason:** OpCache cannot optimize mounted code files, causing severe performance degradation (265ms → 14000ms page load)

**Trade-off:** Need container rebuild for code changes, but acceptable for production stability.

---

## 📚 Documentation Structure

All documentation files in `doc/` folder:

### Core Documentation:
- **PROJECT_SUMMARY.md** - Complete project overview
- **STRUCTURE_GUIDE.md** - This file
- **QUICK_START.md** - Getting started guide
- **QUICK_REFERENCE.md** - Command reference
- **DEPLOYMENT_GUIDE.md** - Deployment workflows

### Technical Documentation:
- **SERVER_SPECIFICATIONS.md** - Server requirements
- **DATABASE_BACKUP_GUIDE.md** - Backup procedures
- **DATABASE_CREDENTIALS.md** - Database access
- **DEPLOYMENT_CHECKLIST.md** - Pre/post deployment
- **NGINX_CONFIGURATION.md** - Reverse proxy setup

### Architecture Documentation:
- **VISUAL_ARCHITECTURE.md** - Visual diagrams
- **MERMAID_DIAGRAMS.md** - Interactive diagrams
- **IMAGE_AND_CONTAINER_NAMING.md** - Naming conventions

### Remote Control Documentation:
- **REMOTE_CONTROL_ARCHITECTURE_EXPLAINED.md** - System architecture
- **APK_CONNECTION_GUIDE.md** - Android integration
- **CMS_LOGIN_GUIDE.md** - Admin panel access

### Guides & Best Practices:
- **ENV_BEST_PRACTICES.md** - Environment configuration
- **PRODUCTION_PERFORMANCE_GUIDE.md** - Performance tuning
- **PERFORMANCE_OPTIMIZATIONS.md** - Optimization strategies
- **LOAD_BALANCING_GUIDE.md** - Load balancer configuration
- **SECURITY_AND_HEALTH_CHECK_IMPROVEMENTS.md** - Security guide

**Total:** 27 documentation files (organized & consolidated)

---

## 🎯 Benefits of This Structure

### ✅ Centralized Data Management
- All runtime data in one place (`data-kiosk/`)
- Easy to backup: just backup `data-kiosk/` folder
- Easy to restore: restore `data-kiosk/` folder
- Clear separation from application code

### ✅ Easy Backup & Restore

```bash
# Backup all data
tar -czf kiosk-backup-$(date +%Y%m%d).tar.gz data-kiosk/

# Restore
tar -xzf kiosk-backup-20260129.tar.gz
```

Or use integrated backup:
```bash
# Backup during deployment
./deploy-prod.sh --backup

# Manual backup
./backup-database.sh
```

### ✅ Easy Migration

Move to another server:
```bash
# On old server
tar -czf kiosk-data.tar.gz data-kiosk/

# On new server
tar -xzf kiosk-data.tar.gz
./deploy-prod.sh
```

### ✅ Organized Documentation

All documentation in `doc/` folder:
- Easy to find
- Version controlled
- Separate from code and data
- Reduced from 39 → 27 files (better organized)

### ✅ .gitignore Friendly

```gitignore
# Add to .gitignore
data-kiosk/
!data-kiosk/nginx/nginx.conf
```

This keeps:
- Runtime data out of git
- Configuration in git
- Clean repository

---

## 🔧 Directory Creation

Directories are automatically created by deployment scripts:

**deploy-dev.sh:**
```bash
mkdir -p data-kiosk/logs/cosmic-app
mkdir -p data-kiosk/logs/cosmic-queue-1
mkdir -p data-kiosk/logs/cosmic-scheduler
mkdir -p data-kiosk/logs/generate-pdf
mkdir -p data-kiosk/logs/remote-android
mkdir -p data-kiosk/logs/remote-relay
mkdir -p data-kiosk/backups
mkdir -p data-kiosk/minio-backup
mkdir -p data-kiosk/mariadb
mkdir -p data-kiosk/redis
mkdir -p data-kiosk/minio
```

**deploy-prod.sh:** (adds queue-2 and nginx)
```bash
mkdir -p data-kiosk/logs/cosmic-queue-2
mkdir -p data-kiosk/nginx/ssl
mkdir -p data-kiosk/nginx/logs
# ... same as dev
```

---

## 🛠️ Maintenance Tasks

### View Logs

```bash
# Cosmic app logs
tail -f data-kiosk/logs/cosmic-app/*.log

# Generate PDF logs
tail -f data-kiosk/logs/generate-pdf/*.log

# Remote relay logs
tail -f data-kiosk/logs/remote-relay/*.log

# Nginx logs
tail -f data-kiosk/nginx/logs/access.log
tail -f data-kiosk/nginx/logs/error.log
```

### Database Backup

```bash
# Automated backup during deployment
./deploy-prod.sh --backup

# Manual backup
./backup-database.sh

# List backups
ls -lh data-kiosk/backups/*.gz
```

### Clean Old Logs

```bash
# Remove logs older than 30 days
find data-kiosk/logs/ -name "*.log" -mtime +30 -delete
```

### MinIO Backup

```bash
# Backup MinIO data
docker compose -f docker-compose.prod.yml exec minio \
  mc mirror /data /backup
```

---

## 📋 File Structure Quick Reference

| Category | Location | Purpose |
|----------|----------|---------|
| **Application Code** | `cosmic-media-streaming-dpr/`, `generate-pdf/`, `remote-android-device/`, `remote-control-relay/` | Source code |
| **Runtime Data** | `data-kiosk/` | All persistent data |
| **Documentation** | `doc/` | All .md files (27 files) |
| **Configuration** | `.env.dev`, `.env.prod`, `docker-compose.*.yml` | Environment & orchestration |
| **Deployment** | `deploy-dev.sh`, `deploy-prod.sh`, `backup-database.sh` | Automation scripts |
| **Database** | `platform.sql`, `restore.sql` | Database initialization |

---

## 🔄 Migration from Old Structure

If you have old folders (`logs/`, `backups/`, `nginx/`, `minio-backup/`):

```bash
# Stop services
docker compose -f docker-compose.prod.yml down

# Move to data-kiosk
mkdir -p data-kiosk
[ -d logs ] && mv logs data-kiosk/
[ -d backups ] && mv backups data-kiosk/
[ -d nginx ] && mv nginx data-kiosk/
[ -d minio-backup ] && mv minio-backup data-kiosk/

# Start services with new structure
./deploy-prod.sh
```

---

## 🧹 Recent Cleanup

### Files Deleted (Redundant):

**Server Specifications (merged):**
- ❌ `SERVER_SPECIFICATIONS_MICROSERVICES.md` → merged to `SERVER_SPECIFICATIONS.md`
- ❌ `SERVER_SPECIFICATIONS_MICROSERVICES_DOCX.md` → duplicate

**Summary Files (merged):**
- ❌ `SUMMARY.md` → merged to `PROJECT_SUMMARY.md`
- ❌ `EXECUTIVE_SUMMARY.md` → merged to `PROJECT_SUMMARY.md`
- ❌ `CLEANUP_SUMMARY.md` → merged to `PROJECT_SUMMARY.md`
- ❌ `REORGANIZATION_SUMMARY.md` → merged to `PROJECT_SUMMARY.md`

**Structure Files (merged):**
- ❌ `FILE_STRUCTURE.md` → merged to `STRUCTURE_GUIDE.md`
- ❌ `FOLDER_STRUCTURE.md` → merged to `STRUCTURE_GUIDE.md`

**HTML Files (redundant):**
- ❌ `TECHNICAL_DOCUMENTATION.html.backup` → backup file
- ❌ `MIGRATION_AND_MAINTENANCE_GUIDE.html` → MD version exists
- ❌ `TECHNICAL_DOCUMENTATION_UPDATE_SECTION.html` → outdated
- ❌ `TECHNICAL_UPDATES_JAN2026.html` → merged to main docs

**Result:** From 39 files → **27 files** (30% reduction, better organized)

---

## ✨ Summary

**Before:** Scattered files and folders  
**After:** Clean, organized, professional structure

- ✅ All data in `data-kiosk/`
- ✅ All docs in `doc/` (consolidated from 39 → 27 files)
- ✅ Easy backup & restore
- ✅ Clear separation of concerns
- ✅ Git-friendly
- ✅ Performance optimized (strategic volume mounts)

**Perfect for production deployment! 🎉**

---

**Date:** 29 Januari 2026  
**Status:** ✅ Complete & Optimized  
**Version:** 2.0.0
