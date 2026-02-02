# Backend Fixes - Docker Production Deployment Guide

**Target Environment**: Docker Production (docker-compose.prod.yml)  
**Date**: February 2, 2026  
**Status**: Ready for Deployment  

---

## 🐳 DOCKER ENVIRONMENT OVERVIEW

### Current Running Containers

```
cosmic-app-1-prod          → Laravel App (Instance 1)
cosmic-app-2-prod          → Laravel App (Instance 2)  
cosmic-app-3-prod          → Laravel App (Instance 3)
cosmic-scheduler-prod      → Laravel Scheduler (Cron)
cosmic-queue-*-prod        → Queue Workers
platform-db-prod           → MariaDB Database
platform-redis-prod        → Redis Cache
platform-nginx-prod        → Nginx Load Balancer
```

### Key Services for This Deployment

1. **cosmic-app-* containers**: Handle device heartbeat requests
2. **cosmic-scheduler-prod**: Runs scheduled commands every minute
3. **platform-db-prod**: Requires migration
4. **platform-redis-prod**: Caching layer

---

## 🚀 DEPLOYMENT PROCEDURE

### Option 1: Automated Deployment (Recommended)

```bash
# 1. Make script executable
chmod +x deploy_backend_fixes_docker.sh

# 2. Run deployment script
bash deploy_backend_fixes_docker.sh
```

The script will:
- ✅ Check container health
- ✅ Backup database
- ✅ Rebuild Docker images
- ✅ Run migration
- ✅ Clear caches
- ✅ Restart services
- ✅ Verify deployment

---

### Option 2: Manual Deployment (Step-by-Step)

#### Step 1: Backup Database

```bash
# Create backup
docker exec platform-db-prod mysqldump -u root -p${DB_ROOT_PASSWORD} platform \
  > ./data-kiosk/backups/pre-migration-$(date +%Y%m%d_%H%M%S).sql
```

#### Step 2: Rebuild Docker Images

```bash
# Rebuild cosmic-app images with new code
docker-compose -f docker-compose.prod.yml build cosmic-app-1 cosmic-app-2 cosmic-app-3

# Rebuild scheduler if needed
docker-compose -f docker-compose.prod.yml build cosmic-scheduler
```

#### Step 3: Run Migration

```bash
# Run migration in one app container
docker exec -it cosmic-app-1-prod php artisan migrate --force

# Verify migration
docker exec cosmic-app-1-prod php artisan migrate:status
```

**Expected Output:**
```
Migration table created successfully.
Migrating: 2026_02_02_000001_add_heartbeat_management_fields_to_remotes
Migrated:  2026_02_02_000001_add_heartbeat_management_fields_to_remotes (XX.XXms)
```

#### Step 4: Clear Caches

```bash
docker exec cosmic-app-1-prod php artisan cache:clear
docker exec cosmic-app-1-prod php artisan config:clear
docker exec cosmic-app-1-prod php artisan route:clear
```

#### Step 5: Restart Services

```bash
# Restart app containers (zero-downtime with 3 replicas)
docker-compose -f docker-compose.prod.yml restart cosmic-app-1
sleep 10
docker-compose -f docker-compose.prod.yml restart cosmic-app-2
sleep 10
docker-compose -f docker-compose.prod.yml restart cosmic-app-3

# Restart scheduler to load new command
docker-compose -f docker-compose.prod.yml restart cosmic-scheduler
```

#### Step 6: Verify Deployment

```bash
# Check if command is registered
docker exec cosmic-app-1-prod php artisan list | grep devices:monitor

# Check scheduler configuration
docker exec cosmic-scheduler-prod php artisan schedule:list

# Test command execution
docker exec cosmic-app-1-prod php artisan devices:monitor-status --dry-run --verbose
```

---

## 🔍 VERIFICATION CHECKLIST

### 1. Database Schema

```bash
# Check new columns exist
docker exec platform-db-prod mysql -u root -p${DB_ROOT_PASSWORD} platform \
  -e "DESCRIBE remotes;" | grep heartbeat
```

**Expected:** Should show new columns like `heartbeat_interval_seconds`, `grace_period_seconds`, etc.

### 2. Application Health

```bash
# Check app containers are healthy
docker ps | grep cosmic-app

# Check health endpoint
curl http://localhost:8080/api/health
```

### 3. Scheduler Status

```bash
# Check scheduler is running
docker logs --tail 50 cosmic-scheduler-prod

# Should see schedule:run executing every minute
```

### 4. Device Monitoring

```bash
# Run status monitor manually
docker exec cosmic-app-1-prod php artisan devices:monitor-status --verbose

# Check output for status transitions
```

### 5. Heartbeat Testing

```bash
# Test heartbeat endpoint (replace TOKEN)
curl -X POST http://localhost:8080/api/devices/heartbeat \
  -H "Authorization: Bearer YOUR_DEVICE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"battery_level": 85}'

# Should return JSON with should_reconnect field
```

---

## 📊 MONITORING AFTER DEPLOYMENT

### Watch Device Status Changes

```bash
# Real-time log monitoring
docker exec -it cosmic-app-1-prod tail -f /var/www/storage/logs/laravel.log \
  | grep "Device status"
```

**Look for:**
```
[timestamp] Device status transition {
  "device_id": 123,
  "device_name": "KIOSK-01",
  "from_status": "Connected",
  "to_status": "Temporarily Offline",
  "reason": "No heartbeat for 45s (grace period: 60s)",
  "source": "system"
}
```

### Monitor Scheduler Execution

```bash
# Watch scheduler logs
docker logs -f cosmic-scheduler-prod
```

**Every minute should show:**
```
Running scheduled command: Artisan devices:monitor-status
```

### Check Application Logs

```bash
# App container logs
docker logs -f cosmic-app-1-prod

# Scheduler logs
docker logs -f cosmic-scheduler-prod

# Database logs
docker logs -f platform-db-prod
```

### Database Monitoring

```sql
-- Connect to database
docker exec -it platform-db-prod mysql -u root -p${DB_ROOT_PASSWORD} platform

-- Check recent status changes
SELECT id, name, status, previous_status, 
       last_status_change_at, status_change_reason, last_heartbeat_source
FROM remotes 
WHERE last_status_change_at > NOW() - INTERVAL 1 HOUR
ORDER BY last_status_change_at DESC;

-- Check devices with flapping
SELECT name, COUNT(*) as transitions
FROM remotes
WHERE last_status_change_at > NOW() - INTERVAL 5 MINUTE
GROUP BY id
HAVING transitions > 3;
```

---

## 🛠️ DOCKER-SPECIFIC COMMANDS

### Execute Commands in Containers

```bash
# Run artisan commands
docker exec cosmic-app-1-prod php artisan devices:monitor-status --verbose

# Access container shell
docker exec -it cosmic-app-1-prod bash

# Run tinker
docker exec -it cosmic-app-1-prod php artisan tinker
```

### View Logs

```bash
# Application logs (Laravel)
docker exec cosmic-app-1-prod cat /var/www/storage/logs/laravel.log | tail -100

# Scheduler logs
docker logs cosmic-scheduler-prod --tail 100 --follow

# Database logs
docker logs platform-db-prod --tail 50
```

### Restart Services

```bash
# Restart single container
docker-compose -f docker-compose.prod.yml restart cosmic-app-1

# Restart all app containers
docker-compose -f docker-compose.prod.yml restart cosmic-app-1 cosmic-app-2 cosmic-app-3

# Restart scheduler
docker-compose -f docker-compose.prod.yml restart cosmic-scheduler

# Full restart (use with caution)
docker-compose -f docker-compose.prod.yml restart
```

---

## ⚠️ TROUBLESHOOTING

### Issue: Migration Fails

```bash
# Check migration status
docker exec cosmic-app-1-prod php artisan migrate:status

# If migration is stuck, rollback and retry
docker exec cosmic-app-1-prod php artisan migrate:rollback --step=1
docker exec cosmic-app-1-prod php artisan migrate --force
```

### Issue: Command Not Found

```bash
# Clear config cache
docker exec cosmic-app-1-prod php artisan config:clear

# Verify file exists
docker exec cosmic-app-1-prod ls -la /var/www/app/Console/Commands/DeviceStatusMonitorCommand.php

# Rebuild image
docker-compose -f docker-compose.prod.yml build cosmic-app-1
docker-compose -f docker-compose.prod.yml restart cosmic-app-1
```

### Issue: Scheduler Not Running Command

```bash
# Check scheduler container
docker logs cosmic-scheduler-prod --tail 100

# Verify schedule configuration
docker exec cosmic-scheduler-prod php artisan schedule:list

# Check if scheduler is actually running
docker exec cosmic-scheduler-prod ps aux | grep "schedule:run"
```

### Issue: High Database Load

```bash
# Check active connections
docker exec platform-db-prod mysql -u root -p${DB_ROOT_PASSWORD} \
  -e "SHOW PROCESSLIST;"

# Check slow queries
docker exec platform-db-prod mysql -u root -p${DB_ROOT_PASSWORD} \
  -e "SELECT * FROM information_schema.processlist WHERE time > 5;"
```

### Issue: Containers Unhealthy

```bash
# Check container health
docker ps | grep cosmic-app

# Check health endpoint
docker exec cosmic-app-1-prod curl -f http://localhost:80/api/health

# Restart unhealthy containers
docker-compose -f docker-compose.prod.yml restart cosmic-app-1
```

---

## 🔄 ROLLBACK PROCEDURE

If issues occur after deployment:

### 1. Rollback Migration

```bash
docker exec cosmic-app-1-prod php artisan migrate:rollback --step=1
```

### 2. Restore Database Backup

```bash
# Find backup file
ls -lt ./data-kiosk/backups/

# Restore
docker exec -i platform-db-prod mysql -u root -p${DB_ROOT_PASSWORD} platform \
  < ./data-kiosk/backups/pre-migration-YYYYMMDD_HHMMSS.sql
```

### 3. Revert Code Changes

```bash
# Checkout previous commit
cd cosmic-media-streaming-dpr
git stash  # Save changes
git log --oneline  # Find commit before changes
git checkout <previous-commit>

# Rebuild images
cd ..
docker-compose -f docker-compose.prod.yml build cosmic-app-1 cosmic-app-2 cosmic-app-3
docker-compose -f docker-compose.prod.yml restart cosmic-app-1 cosmic-app-2 cosmic-app-3
```

### 4. Disable New Features

```bash
# Remove middleware from route
docker exec cosmic-app-1-prod bash -c "sed -i 's/->middleware.*/;/' /var/www/routes/api.php"

# Comment out scheduled command in Kernel.php
# (requires code edit and rebuild)
```

---

## 📋 POST-DEPLOYMENT CHECKLIST

24 hours after deployment:

- [ ] No migration errors in logs
- [ ] Device status transitions working correctly
- [ ] No status flapping reported
- [ ] Heartbeat rate limiting working (check for 429 responses)
- [ ] Scheduler running every minute
- [ ] Database performance normal
- [ ] Cache hit ratio improved
- [ ] No increase in error rates
- [ ] External Python service coordinated (if updated)

---

## 🎯 DOCKER-SPECIFIC NOTES

### Load Balancing

With 3 app replicas (cosmic-app-1/2/3), device heartbeats will be distributed across containers. This is fine because:
- All share the same database
- Status updates use row-level locking
- Cache is centralized in Redis

### Scheduler Considerations

- Only ONE scheduler container should run to avoid duplicate executions
- The `cosmic-scheduler-prod` container already has `withoutOverlapping()` protection
- Scheduler runs every 60 seconds, executing `devices:monitor-status` every minute

### Volume Mounts

Important mounts for this deployment:
```yaml
# Database migrations (mounted read-only)
- ./cosmic-media-streaming-dpr/database/migrations:/var/www/database/migrations:ro

# Application logs (persistent)
- ./data-kiosk/logs/cosmic-app-X:/var/www/storage/logs
```

### Container Restart Strategy

For zero-downtime deployment with 3 replicas:
1. Rebuild all images
2. Restart one container at a time
3. Wait 10-15 seconds between restarts
4. Nginx load balancer will route traffic to healthy containers

---

## 📞 SUPPORT

**Logs Location:**
- Application: `./data-kiosk/logs/cosmic-app-*/laravel.log`
- Scheduler: `docker logs cosmic-scheduler-prod`
- Database: `docker logs platform-db-prod`

**Documentation:**
- `IMPLEMENTATION_BACKEND_FIXES.md` - Complete technical docs
- `QUICK_REFERENCE.md` - Commands & troubleshooting
- `ARCHITECTURE_DIAGRAMS.md` - Visual reference
- `DOCKER_DEPLOYMENT_GUIDE.md` - This file

**Test Commands:**
```bash
# Quick health check
docker exec cosmic-app-1-prod php artisan devices:monitor-status --dry-run

# View recent changes
docker exec cosmic-app-1-prod php artisan tinker --execute="Remote::whereNotNull('last_status_change_at')->latest('last_status_change_at')->take(5)->get(['id','name','status','previous_status','status_change_reason'])"
```

---

**Ready for production deployment in Docker environment** 🚀
