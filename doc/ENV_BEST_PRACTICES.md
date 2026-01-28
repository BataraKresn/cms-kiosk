# 🔧 Environment Variables - Best Practices

## Perubahan Penting: Single Source of Truth

Environment variables sekarang **hanya** didefinisikan di **satu tempat**: file `.env.dev` atau `.env.prod`.

Docker Compose files menggunakan `env_file` directive, **bukan** `environment:` yang redundant.

---

## 📁 File Structure

```
/home/ubuntu/kiosk/
│
├── .env.example          # Template (reference only)
├── .env.dev              # Development environment (use this!)
├── .env.prod             # Production environment (customize passwords!)
│
├── docker-compose.dev.yml   # Uses: .env.dev
├── docker-compose.prod.yml  # Uses: .env.prod
│
└── cosmic-media-streaming-dpr/
    ├── .env.dev          # Standalone development
    └── docker-compose.dev.yml  # Uses: .env.dev
```

---

## ✅ Keuntungan

### ❌ Before (Bad - Double Configuration):

```yaml
# docker-compose.yml
services:
  app:
    environment:
      - DB_HOST=mariadb          # ❌ Redundant
      - DB_PORT=3306             # ❌ Redundant
      - DB_DATABASE=platform     # ❌ Redundant
      - DB_USERNAME=user         # ❌ Redundant
      - DB_PASSWORD=password     # ❌ Redundant
      # ... 30+ more variables
```

```bash
# .env
DB_HOST=mariadb                  # ❌ Double definition!
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=user
DB_PASSWORD=password
```

**Masalah:**
- ❌ Harus update 2 tempat
- ❌ Mudah tidak sinkron
- ❌ File docker-compose.yml terlalu panjang
- ❌ Sulit maintain

### ✅ After (Good - Single Source):

```yaml
# docker-compose.dev.yml
services:
  cosmic-app:
    env_file:
      - .env.dev            # ✅ All variables from here!
    # Clean and simple!
  
  cosmic-queue:
    env_file:
      - .env.dev            # ✅ Reuse same config
```

```bash
# .env.dev
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=platform
DB_USERNAME=platform_user
DB_PASSWORD=platform_password_dev
# ... semua variables di satu tempat
```

**Keuntungan:**
- ✅ Single source of truth
- ✅ Easy to manage
- ✅ Clean docker-compose files
- ✅ No redundancy
- ✅ Update sekali, apply ke semua services

---

## 🚀 Usage

### Development

```bash
# File yang digunakan: .env.dev (sudah configured dengan default yang aman)
./deploy-dev.sh
```

Docker Compose akan otomatis load variables dari `.env.dev`.

### Production

```bash
# 1. Edit .env.prod (GANTI PASSWORDS!)
nano .env.prod

# 2. Deploy
./deploy-prod.sh
```

Docker Compose akan otomatis load variables dari `.env.prod`.

---

## 📝 Exception: MariaDB & MinIO

**Note:** MariaDB dan MinIO masih menggunakan `environment:` di docker-compose karena mereka butuh variabel khusus untuk initialization:

```yaml
mariadb:
  environment:
    MARIADB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    MARIADB_DATABASE: ${DB_DATABASE}
    MARIADB_USER: ${DB_USERNAME}
    MARIADB_PASSWORD: ${DB_PASSWORD}
```

Ini **OK** karena values-nya diambil dari `.env.dev` atau `.env.prod` menggunakan `${VAR}` syntax.

---

## 🔐 Database Field Mapping

Beberapa services pakai naming berbeda untuk database fields:

```yaml
# Generate PDF Service (Node.js)
environment:
  - DB_USER=${DB_USERNAME}     # Map DB_USERNAME → DB_USER
  - DB_NAME=${DB_DATABASE}     # Map DB_DATABASE → DB_NAME

# Remote Android Service (Python)
environment:
  - DB_USER=${DB_USERNAME}     # Map DB_USERNAME → DB_USER  
  - DB_NAME=${DB_DATABASE}     # Map DB_DATABASE → DB_NAME
```

Ini diperlukan karena:
- Laravel uses: `DB_USERNAME` dan `DB_DATABASE`
- Node.js expects: `DB_USER` dan `DB_NAME`
- Python expects: `DB_USER` dan `DB_NAME`

Mapping ini dilakukan di docker-compose, jadi tetap clean di `.env` file.

---

## 📋 Quick Reference

### All Environment Variables Location:

| Deployment Type | File | Location |
|----------------|------|----------|
| Development (all services) | `.env.dev` | `/home/ubuntu/kiosk/.env.dev` |
| Production (all services) | `.env.prod` | `/home/ubuntu/kiosk/.env.prod` |
| Cosmic Media Dev (standalone) | `.env.dev` | `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/.env.dev` |
| Template/Reference | `.env.example` | `/home/ubuntu/kiosk/.env.example` |

### Files Changed:

- ✅ `docker-compose.dev.yml` - Uses `env_file: - .env.dev`
- ✅ `docker-compose.prod.yml` - Uses `env_file: - .env.prod`
- ✅ `cosmic-media-streaming-dpr/docker-compose.dev.yml` - Uses `env_file: - .env.dev`
- ✅ `deploy-dev.sh` - Checks for `.env.dev`
- ✅ `deploy-prod.sh` - Checks for `.env.prod`
- ✅ `cosmic-media-streaming-dpr/deploy-dev.sh` - Checks for `.env.dev`

---

## 🎯 Summary

**Before:** Environment variables defined in 2 places (docker-compose + .env)
**After:** Environment variables defined in 1 place (.env.dev or .env.prod)

**Result:** 
- ✅ Cleaner code
- ✅ Easier maintenance
- ✅ Single source of truth
- ✅ No more double configuration!

---

**Last Updated:** January 22, 2026  
**Version:** 2.0.0 (Environment Refactor)
