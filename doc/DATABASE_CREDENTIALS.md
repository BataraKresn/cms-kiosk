# 🔐 Database Credentials - Correct Configuration

## Summary

Database credentials are now **correctly configured** and synced with MariaDB container credentials.

---

## 🎯 Understanding the Structure

### When you run `deploy-dev.sh`:
- Uses: `/home/ubuntu/kiosk/.env.dev`
- MariaDB container creates user: `kiosk_user` with password: `Fy9wSV1082Ml`
- All services MUST use these credentials to connect

### When you run `deploy-prod.sh`:
- Uses: `/home/ubuntu/kiosk/.env.prod`
- MariaDB container creates user: `kiosk_platform` with password: `5Kh3dY82ry05`
- All services MUST use these credentials to connect

---

## 📋 Correct Credentials

### Development Environment
**Used by:** `deploy-dev.sh` → `docker-compose.dev.yml` → `.env.dev`

| Field | Value |
|-------|-------|
| **Database Name** | `platform` |
| **Username** | `kiosk_user` |
| **Password** | `Fy9wSV1082Ml` |
| **Host** | `100.81.53.100` (Tailscale IP) |
| **Port** | `3306` |

### Production Environment (Docker Compose)
**Used by:** `deploy-prod.sh` → `docker-compose.prod.yml` → `.env.prod`

| Field | Value |
|-------|-------|
| **Database Name** | `platform` |
| **Username** | `kiosk_platform` |
| **Password** | `5Kh3dY82ry05` |
| **Host** | `mariadb` (Docker network) |
| **Port** | `3306` |

### Production Environment (Standalone - cosmic-media only)
**Used by:** `cosmic-media-streaming-dpr/.env` (standalone deployment)

| Field | Value |
|-------|-------|
| **Database Name** | `platform` |
| **Username** | `platform_user` |
| **Password** | `SorryThisIsSuperSecret!!!_DPR` |
| **Host** | `127.0.0.1` (local MySQL, different server) |
| **Port** | `3306` |

---

## 📁 File Configuration

### 1. Root Level (MariaDB Container Credentials)

#### `/home/ubuntu/kiosk/.env.dev` ⭐ **MariaDB DEV**
```bash
# MariaDB will CREATE these credentials
DB_HOST=100.81.53.100         # Tailscale IP (external access)
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=kiosk_user        # ⭐ Services must use this
DB_PASSWORD=Fy9wSV1082Ml      # ⭐ Services must use this
```

#### `/home/ubuntu/kiosk/.env.prod` ⭐ **MariaDB PROD**
```bash
# MariaDB will CREATE these credentials
DB_HOST=mariadb               # Docker network hostname
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=kiosk_platform    # ⭐ Services must use this
DB_PASSWORD=5Kh3dY82ry05      # ⭐ Services must use this
```

---

### 2. Cosmic Media Streaming (Laravel)

#### `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env.dev`
**Purpose:** Development (with docker-compose.dev.yml or standalone)
**MUST MATCH:** `.env.dev` credentials

```bash
DB_HOST=100.81.53.100         # Same as .env.dev
DB_USERNAME=kiosk_user        # ✅ Matches MariaDB DEV
DB_PASSWORD=Fy9wSV1082Ml      # ✅ Matches MariaDB DEV
```

#### `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env`
**Purpose:** ⭐ **Standalone Production** (different MySQL server)
**Independent:** Uses separate MySQL (not Docker MariaDB)

```bash
DB_HOST=127.0.0.1             # Different server (local MySQL)
DB_USERNAME=platform_user     # Different credentials
DB_PASSWORD=SorryThisIsSuperSecret!!!_DPR
```

---

### 3. Generate PDF (Node.js)

#### `/home/ubuntu/kiosk/generate-pdf/.env`
**Purpose:** Development
**MUST MATCH:** `.env.dev` credentials

```bash
DB_HOST="100.81.53.100"       # Same as .env.dev
DB_USER="kiosk_user"          # ✅ Matches MariaDB DEV
DB_PASSWORD="Fy9wSV1082Ml"    # ✅ Matches MariaDB DEV
DB_NAME="platform"
```

#### `/home/ubuntu/kiosk/generate-pdf/.env.prod`
**Purpose:** Production (with docker-compose.prod.yml)
**MUST MATCH:** `.env.prod` credentials

```bash
DB_HOST="mariadb"             # Same as .env.prod
DB_USER="kiosk_platform"      # ✅ Matches MariaDB PROD
DB_PASSWORD="5Kh3dY82ry05"    # ✅ Matches MariaDB PROD
DB_NAME="platform"
```

---

## 🔄 Connection Flow

### Development (`./deploy-dev.sh`)

```
1. deploy-dev.sh loads .env.dev
   ↓
2. docker-compose.dev.yml uses .env.dev
   ↓
3. MariaDB container creates:
   - User: kiosk_user
   - Password: Fy9wSV1082Ml
   - Database: platform
   ↓
4. Services connect:
   - cosmic-app reads .env.dev → ✅ kiosk_user
   - generate-pdf reads .env → ✅ kiosk_user
   ↓
5. All services connected to MariaDB @ 100.81.53.100
```

### Production (`./deploy-prod.sh`)

```
1. deploy-prod.sh loads .env.prod
   ↓
2. docker-compose.prod.yml uses .env.prod
   ↓
3. MariaDB container creates:
   - User: kiosk_platform
   - Password: 5Kh3dY82ry05
   - Database: platform
   ↓
4. Services connect:
   - cosmic-app reads .env.prod → ✅ kiosk_platform
   - generate-pdf reads .env.prod → ✅ kiosk_platform
   ↓
5. All services connected to MariaDB @ mariadb (Docker network)
```

### Standalone Production (cosmic-media only)

```
cosmic-media-streaming-dpr/.env
   ↓
Uses different MySQL server @ 127.0.0.1
   ↓
Credentials: platform_user / SorryThisIsSuperSecret!!!_DPR
   ↓
Independent from Docker MariaDB
```

---

## 🎯 Key Points

### ✅ Correct Understanding:

1. **MariaDB credentials** are defined in:
   - DEV: `/home/ubuntu/kiosk/.env.dev`
   - PROD: `/home/ubuntu/kiosk/.env.prod`

2. **All services** MUST use credentials from:
   - DEV services → use `.env.dev` credentials
   - PROD services → use `.env.prod` credentials

3. **cosmic-media-streaming-dpr/.env** is DIFFERENT:
   - Standalone production
   - Uses external MySQL @ 127.0.0.1
   - Different credentials (platform_user)

### ❌ Common Mistake (Fixed):

**Before (Wrong):**
- Tried to sync all credentials to `platform_user / SorryThisIsSuperSecret!!!_DPR`
- This doesn't match MariaDB container credentials
- Services couldn't connect to MariaDB

**After (Correct):**
- DEV: All use `kiosk_user / Fy9wSV1082Ml`
- PROD: All use `kiosk_platform / 5Kh3dY82ry05`
- Standalone cosmic-media: Uses `platform_user` (different server)

---

## 📊 Summary Table

| Environment | DB Host | Username | Password | Files |
|-------------|---------|----------|----------|-------|
| **DEV** | `100.81.53.100` | `kiosk_user` | `Fy9wSV1082Ml` | `.env.dev`, `cosmic-media/.env.dev`, `generate-pdf/.env` |
| **PROD (Docker)** | `mariadb` | `kiosk_platform` | `5Kh3dY82ry05` | `.env.prod`, `generate-pdf/.env.prod` |
| **PROD (Standalone)** | `127.0.0.1` | `platform_user` | `SorryThisIsSuperSecret!!!_DPR` | `cosmic-media/.env` |

---

## ✅ Verification

```bash
# Test DEV connection
./deploy-dev.sh
docker compose -f docker-compose.dev.yml exec cosmic-app \
  php artisan db:show

# Test PROD connection (Docker)
./deploy-prod.sh
docker compose -f docker-compose.prod.yml exec cosmic-app \
  php artisan db:show

# Test standalone production
cd cosmic-media-streaming-dpr
php artisan db:show
```

---

**Last Updated:** January 22, 2025  
**Version:** 3.1.0 (Fixed)

---

## 📋 Database Credentials (Uniform)

| Field | Value |
|-------|-------|
| **Database Name** | `platform` |
| **Username** | `platform_user` |
| **Password** | `SorryThisIsSuperSecret!!!_DPR` |
| **Port** | `3306` |

---

## 🌐 Database Host per Environment

### Development (DEV)
**Host:** `100.81.53.100` (Tailscale IP)

**Files using DEV config:**
- `/home/ubuntu/kiosk/.env.dev`
- `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env.dev`
- `/home/ubuntu/kiosk/generate-pdf/.env`

### Production (PROD)
**Host:** `127.0.0.1` (localhost/local MySQL)

**Files using PROD config:**
- `/home/ubuntu/kiosk/.env.prod`
- `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env` ⭐
- `/home/ubuntu/kiosk/generate-pdf/.env.prod`

---

## 📁 File Breakdown

### 1. Root Level (Orchestration)

#### `/home/ubuntu/kiosk/.env.dev`
```bash
# For: docker-compose.dev.yml (all services)
DB_HOST=100.81.53.100         # Tailscale IP
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=platform_user
DB_PASSWORD=SorryThisIsSuperSecret!!!_DPR
```

#### `/home/ubuntu/kiosk/.env.prod`
```bash
# For: docker-compose.prod.yml (all services)
DB_HOST=127.0.0.1             # Local MySQL
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=platform_user
DB_PASSWORD=SorryThisIsSuperSecret!!!_DPR
```

---

### 2. Cosmic Media Streaming (Laravel)

#### `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env`
**Purpose:** ⭐ **PRODUCTION** (Currently Active)
```bash
APP_ENV=production
APP_DEBUG=false
DB_HOST=127.0.0.1             # Local MySQL
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=platform_user
DB_PASSWORD=SorryThisIsSuperSecret!!!_DPR
```

#### `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env.dev`
**Purpose:** Standalone development
```bash
APP_ENV=local
APP_DEBUG=true
DB_HOST=100.81.53.100         # Tailscale IP
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=platform_user
DB_PASSWORD=SorryThisIsSuperSecret!!!_DPR
```

---

### 3. Generate PDF (Node.js)

#### `/home/ubuntu/kiosk/generate-pdf/.env`
**Purpose:** **DEVELOPMENT** (Dev/Testing)
```bash
# DEV - Uses Tailscale IP
DB_HOST="100.81.53.100"       # Tailscale IP
DB_PORT=3306
DB_USER="platform_user"       # Note: Uses DB_USER (not DB_USERNAME)
DB_PASSWORD="SorryThisIsSuperSecret!!!_DPR"
DB_NAME="platform"            # Note: Uses DB_NAME (not DB_DATABASE)
```

#### `/home/ubuntu/kiosk/generate-pdf/.env.prod`
**Purpose:** **PRODUCTION** (Created new)
```bash
# PROD - Uses Local MySQL
DB_HOST="127.0.0.1"           # Local MySQL
DB_PORT=3306
DB_USER="platform_user"
DB_PASSWORD="SorryThisIsSuperSecret!!!_DPR"
DB_NAME="platform"
```

---

## 🔍 Key Differences

### Laravel (cosmic-media-streaming-dpr)
Uses:
- `DB_USERNAME` (not DB_USER)
- `DB_DATABASE` (not DB_NAME)

### Node.js (generate-pdf)
Uses:
- `DB_USER` (not DB_USERNAME)
- `DB_NAME` (not DB_DATABASE)

**Solution:** Docker Compose maps these in `environment:` section:
```yaml
generate-pdf:
  environment:
    - DB_USER=${DB_USERNAME}      # Map to Node.js format
    - DB_NAME=${DB_DATABASE}      # Map to Node.js format
```

---

## 🌐 Connection Flow

### Development Deployment (`./deploy-dev.sh`)

```
┌──────────────────────────────────────┐
│  docker-compose.dev.yml              │
│  Uses: .env.dev                      │
│  DB_HOST=100.81.53.100 (Tailscale)  │
└──────────────────────────────────────┘
              │
        ┌─────┼─────┐
        │           │
    ┌───▼───┐   ┌──▼──────┐
    │Cosmic │   │Generate │
    │ Media │   │  PDF    │
    │       │   │         │
    └───┬───┘   └───┬─────┘
        │           │
        └─────┬─────┘
              │
    ┌─────────▼──────────┐
    │  MySQL on Tailscale│
    │  100.81.53.100:3306│
    │  User: platform_user│
    └────────────────────┘
```

### Production Deployment (cosmic-media-streaming-dpr standalone)

```
┌────────────────────────────┐
│  cosmic-media-streaming-dpr│
│  Uses: .env (production)   │
│  DB_HOST=127.0.0.1         │
└────────────────────────────┘
              │
    ┌─────────▼─────────┐
    │  Local MySQL      │
    │  127.0.0.1:3306   │
    │  User: platform_user│
    └───────────────────┘
```

---

## ✅ Verification

### Test Dev Connection
```bash
# From cosmic-media-streaming-dpr
mysql -h 100.81.53.100 -P 3306 -u platform_user -p platform
# Password: SorryThisIsSuperSecret!!!_DPR

# Test connection
./deploy-dev.sh
docker compose -f docker-compose.dev.yml exec cosmic-app php artisan migrate:status
```

### Test Prod Connection
```bash
# From cosmic-media-streaming-dpr standalone
mysql -h 127.0.0.1 -P 3306 -u platform_user -p platform
# Password: SorryThisIsSuperSecret!!!_DPR
```

---

## 📊 Summary Table

| Environment | DB Host | Files |
|-------------|---------|-------|
| **Development** | `100.81.53.100` | `.env.dev`, `cosmic-media-streaming-dpr/.env.dev`, `generate-pdf/.env` |
| **Production** | `127.0.0.1` | `.env.prod`, `cosmic-media-streaming-dpr/.env`, `generate-pdf/.env.prod` |

**Credentials:** All use `platform_user / SorryThisIsSuperSecret!!!_DPR / platform`

---

## 🎯 Result

✅ **Uniform database credentials** across all services  
✅ **Dev uses Tailscale IP** (100.81.53.100)  
✅ **Prod uses local MySQL** (127.0.0.1)  
✅ **All configs synced and ready**

---

**Last Updated:** January 22, 2025  
**Version:** 3.0.0
