# Docker Image and Container Naming Convention

**Date:** January 23, 2026  
**Status:** Implemented  
**Impact:** All services (dev and prod environments)

---

## Overview

Docker images and containers have been standardized with explicit naming conventions to clearly separate development and production environments. This prevents conflicts and allows both environments to coexist on the same host.

---

## Changes Made

### 1. Image Naming

All services now have explicit `image:` names with environment tags:

**Before:**
```yaml
cosmic-app:
  build:
    context: ./cosmic-media-streaming-dpr
    dockerfile: Dockerfile
  # Image name was auto-generated (inconsistent)
```

**After:**
```yaml
cosmic-app:
  image: kiosk-cosmic-app:prod
  build:
    context: ./cosmic-media-streaming-dpr
    dockerfile: Dockerfile
```

### 2. Files Modified

- `docker-compose.dev.yml` - Added `image:` field with `:dev` tags
- `docker-compose.prod.yml` - Added `image:` field with `:prod` tags

---

## Naming Convention Table

| Service | Dev Image | Dev Container | Prod Image | Prod Container |
|---------|-----------|---------------|------------|----------------|
| **Cosmic Media App** | `kiosk-cosmic-app:dev` | `cosmic-media-app-dev` | `kiosk-cosmic-app:prod` | `cosmic-media-app-prod` |
| **Cosmic Queue** | `kiosk-cosmic-queue:dev` | `cosmic-queue-dev` | `kiosk-cosmic-queue:prod` | `cosmic-queue-1-prod`<br>`cosmic-queue-2-prod` |
| **Cosmic Scheduler** | `kiosk-cosmic-scheduler:dev` | `cosmic-scheduler-dev` | `kiosk-cosmic-scheduler:prod` | `cosmic-scheduler-prod` |
| **Generate PDF** | `kiosk-generate-pdf:dev` | `generate-pdf-dev` | `kiosk-generate-pdf:prod` | `generate-pdf-prod` |
| **Remote Android** | `kiosk-remote-android:dev` | `remote-android-dev` | `kiosk-remote-android:prod` | `remote-android-prod` |

### Infrastructure Services

Infrastructure services (MariaDB, Redis, MinIO, Nginx) use separate containers with environment-specific names but share official images:

| Service | Dev Container | Prod Container | Image |
|---------|---------------|----------------|-------|
| **MariaDB** | `platform-db-dev` | `platform-db-prod` | `mariadb:10.11` |
| **Redis** | `platform-redis-dev` | `platform-redis-prod` | `redis:7-alpine` |
| **MinIO** | `platform-minio-dev` | `platform-minio-prod` | `minio/minio:latest` |
| **Nginx** | N/A | `platform-nginx-prod` | `nginx:alpine` |

---

## Benefits

### 1. **Environment Isolation**
- Dev and prod images are completely separate
- No risk of accidentally using wrong image
- Both environments can coexist on same host

### 2. **Easy Identification**
```bash
$ docker image ls
REPOSITORY                TAG     IMAGE ID       SIZE
kiosk-cosmic-app         dev     abc123def456   1.5GB
kiosk-cosmic-app         prod    789ghi012jkl   1.2GB
kiosk-cosmic-queue       dev     mno345pqr678   1.5GB
kiosk-cosmic-queue       prod    stu901vwx234   1.2GB
```

### 3. **Selective Cleanup**
```bash
# Remove only dev images
docker image rm $(docker image ls -q kiosk-*:dev)

# Remove only prod images
docker image rm $(docker image ls -q kiosk-*:prod)

# Prune unused images (keeps tagged images)
docker image prune -a
```

### 4. **Rollback Support**
```bash
# Tag current prod before update
docker tag kiosk-cosmic-app:prod kiosk-cosmic-app:prod-backup-20260123

# Rollback if needed
docker tag kiosk-cosmic-app:prod-backup-20260123 kiosk-cosmic-app:prod
docker compose -f docker-compose.prod.yml up -d cosmic-app --force-recreate
```

### 5. **Image Management**
```bash
# List images by environment
docker image ls | grep ":dev"
docker image ls | grep ":prod"

# Check image sizes
docker image ls --format "table {{.Repository}}:{{.Tag}}\t{{.Size}}"

# Check image build time
docker image inspect kiosk-cosmic-app:prod | grep Created
```

---

## Usage Examples

### Building Images

**Development:**
```bash
cd /home/ubuntu/kiosk
./deploy-dev.sh
# Creates: kiosk-cosmic-app:dev, kiosk-cosmic-queue:dev, etc.
```

**Production:**
```bash
cd /home/ubuntu/kiosk
./deploy-prod.sh
# Creates: kiosk-cosmic-app:prod, kiosk-cosmic-queue:prod, etc.
```

### Manual Build (if needed)

**Build specific service:**
```bash
# Dev
docker compose -f docker-compose.dev.yml build cosmic-app

# Prod
docker compose -f docker-compose.prod.yml build cosmic-app
```

**Build with no cache:**
```bash
docker compose -f docker-compose.prod.yml build --no-cache cosmic-app
```

### Image Inspection

**Check image layers:**
```bash
docker history kiosk-cosmic-app:prod
```

**Check image details:**
```bash
docker image inspect kiosk-cosmic-app:prod
```

**Compare image sizes:**
```bash
docker image ls kiosk-cosmic-app
```

### Cleanup Operations

**Remove old/unused images:**
```bash
# Remove all untagged images
docker image prune -a

# Remove specific environment images
docker image rm kiosk-cosmic-app:dev
docker image rm kiosk-cosmic-queue:dev

# Remove all dev images at once
docker image ls -q kiosk-*:dev | xargs docker image rm
```

**Remove dangling images:**
```bash
docker image prune
```

---

## Image Size Comparison

### Expected Sizes

| Image | Dev Size | Prod Size | Difference |
|-------|----------|-----------|------------|
| **cosmic-app** | ~1.8GB | ~1.2GB | -600MB (no dev deps, optimized) |
| **cosmic-queue** | ~1.8GB | ~1.2GB | -600MB (same as cosmic-app) |
| **cosmic-scheduler** | ~1.8GB | ~1.2GB | -600MB (same as cosmic-app) |
| **generate-pdf** | ~900MB | ~800MB | -100MB (fewer dependencies) |
| **remote-android** | ~1.1GB | ~990MB | -110MB (Python optimizations) |

### Size Optimization (Production)

Production images are smaller because:
- ✅ `composer install --no-dev` (no dev dependencies)
- ✅ `npm ci --only=production` (no devDependencies after build)
- ✅ Layer caching optimized
- ✅ Build artifacts cleaned up
- ✅ Unnecessary files excluded via `.dockerignore`

---

## Dockerfile Differences

### Development (Dockerfile.dev)

```dockerfile
# Features:
- Hot reload support
- Xdebug enabled
- All dev dependencies
- Source code mounted as volume
- Faster builds (no asset compilation in image)
```

### Production (Dockerfile)

```dockerfile
# Features:
- Multi-stage build (optimized)
- Node.js 20 included for Vite build
- Assets pre-compiled
- No dev dependencies
- Optimized PHP-FPM config
- Nginx included
- Health checks
- Supervisor for process management
```

---

## Troubleshooting

### Issue: Image not found

**Problem:**
```bash
Error: No such image: kiosk-cosmic-app:prod
```

**Solution:**
```bash
# Rebuild the image
cd /home/ubuntu/kiosk
docker compose -f docker-compose.prod.yml build cosmic-app
```

### Issue: Old image still being used

**Problem:**
Container still using old image after rebuild

**Solution:**
```bash
# Force recreate with new image
docker compose -f docker-compose.prod.yml up -d cosmic-app --force-recreate
```

### Issue: Disk space full

**Problem:**
Too many old images consuming disk space

**Solution:**
```bash
# Check disk usage
docker system df

# Clean up old images
docker image prune -a

# Remove specific old images
docker image rm $(docker image ls -q -f "dangling=true")
```

### Issue: Tag mismatch

**Problem:**
Image has wrong tag (`:latest` instead of `:prod`)

**Solution:**
```bash
# Retag the image
docker tag kiosk-cosmic-app:latest kiosk-cosmic-app:prod

# Or rebuild
docker compose -f docker-compose.prod.yml build --no-cache cosmic-app
```

---

## Migration Notes

### From Old Setup

If you have existing containers running with old naming:

1. **Stop old containers:**
   ```bash
   docker compose -f docker-compose.prod.yml down
   ```

2. **Remove old images (optional):**
   ```bash
   docker image rm kiosk-cosmic-app:latest
   docker image rm kiosk-cosmic-queue:latest
   docker image rm kiosk-cosmic-scheduler:latest
   ```

3. **Deploy with new naming:**
   ```bash
   ./deploy-prod.sh
   ```

4. **Verify new images:**
   ```bash
   docker image ls | grep kiosk
   ```

### Data Preservation

**Important:** Data volumes are NOT affected by image changes:
- `data-kiosk/prod/mariadb/` - Database data preserved
- `data-kiosk/prod/redis/` - Redis data preserved
- `data-kiosk/prod/minio/` - MinIO files preserved

---

## Best Practices

### 1. **Always Use Tags**
```bash
# ✅ Good
docker pull kiosk-cosmic-app:prod

# ❌ Bad (uses :latest by default)
docker pull kiosk-cosmic-app
```

### 2. **Backup Before Major Updates**
```bash
# Tag current production image
docker tag kiosk-cosmic-app:prod kiosk-cosmic-app:prod-backup-$(date +%Y%m%d)

# Deploy new version
./deploy-prod.sh

# If issues, rollback
docker tag kiosk-cosmic-app:prod-backup-20260123 kiosk-cosmic-app:prod
```

### 3. **Regular Cleanup**
```bash
# Weekly cleanup of old images
docker image prune -a --filter "until=168h"

# Keep only last 3 backups
docker image ls kiosk-cosmic-app | grep backup | tail -n +4 | awk '{print $3}' | xargs docker image rm
```

### 4. **Monitor Disk Usage**
```bash
# Check Docker disk usage
docker system df

# Detailed breakdown
docker system df -v

# Alert if > 80% full
df -h /var/lib/docker
```

---

## Related Documentation

- [DOCKER_GUIDE.md](DOCKER_GUIDE.md) - Docker setup and configuration
- [DEPLOYMENT_UBUNTU.md](DEPLOYMENT_UBUNTU.md) - Server deployment guide
- [DOCKERFILE_CHANGES.md](DOCKERFILE_CHANGES.md) - Dockerfile optimization history

---

## Summary

This naming convention provides:
- ✅ Clear separation between dev and prod
- ✅ Easy identification and management
- ✅ Safe coexistence of environments
- ✅ Simplified cleanup and maintenance
- ✅ Better version control and rollback

All deploy scripts (`deploy-dev.sh` and `deploy-prod.sh`) automatically use the correct image names.
