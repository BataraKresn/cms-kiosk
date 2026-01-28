# Database Backup & Restore Guide

**Date:** January 23, 2026  
**Status:** Implemented  

---

## Overview

Automated database backup system with weekly schedule. Deployment scripts **no longer** include backup - concerns are separated.

---

## Backup Script

### Location
```
/home/ubuntu/kiosk/backup-database.sh
```

### Features
- ✅ Automated backup with timestamp
- ✅ Compression (gzip)
- ✅ Automatic cleanup (keeps last 4 backups)
- ✅ mysqldump with proper flags
- ✅ Logging to `logs/backup.log`

### Manual Backup
```bash
cd /home/ubuntu/kiosk
./backup-database.sh
```

### Output Example
```
========================================
  Database Backup
  2026-01-23 02:00:00
========================================

✅ Container platform-db-prod is running

📦 Creating database backup...
   Target: data-kiosk/backups/platform_backup_20260123_020000.sql
✅ Backup created successfully
   Size: 148M

📦 Compressing backup...
✅ Backup compressed
   Compressed size: 37M
   Location: data-kiosk/backups/platform_backup_20260123_020000.sql.gz

🧹 Cleaning up old backups...
   Keeping last 4 backups
   Current backups: 5
   Removing 1 old backup(s)...
   - Removing: platform_backup_20260116_020000.sql.gz
✅ Cleanup completed

========================================
  Backup Summary
========================================
✅ Database: platform
✅ Backup file: platform_backup_20260123_020000.sql.gz
✅ Size: 37M
✅ Total backups: 4
```

---

## Setup Cronjob (Weekly Backup)

### 1. Edit Crontab
```bash
crontab -e
```

### 2. Add Backup Schedule

**Every Sunday at 2:00 AM:**
```cron
0 2 * * 0 /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1
```

**Alternative Schedules:**

```cron
# Daily at 2:00 AM
0 2 * * * /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1

# Every 3 days at 2:00 AM
0 2 */3 * * /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1

# Every Monday at 3:00 AM
0 3 * * 1 /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1
```

### 3. Verify Crontab
```bash
crontab -l
```

### 4. Test Cronjob (Optional)
```bash
# Run manually to test
/home/ubuntu/kiosk/backup-database.sh

# Check log
tail -f /home/ubuntu/kiosk/logs/backup.log
```

---

## Restore Database

### Location
```
/home/ubuntu/kiosk/restore-database.sh
```

### Usage

**1. List Available Backups:**
```bash
cd /home/ubuntu/kiosk
./restore-database.sh
```

Output:
```
Available backups:

  data-kiosk/backups/platform_backup_20260123_020000.sql.gz (37M, Jan 23 02:00)
  data-kiosk/backups/platform_backup_20260116_020000.sql.gz (36M, Jan 16 02:00)
  data-kiosk/backups/platform_backup_20260109_020000.sql.gz (35M, Jan 9 02:00)
  data-kiosk/backups/platform_backup_20260102_020000.sql.gz (34M, Jan 2 02:00)

Usage: ./restore-database.sh <backup_file.sql.gz>
```

**2. Restore Specific Backup:**
```bash
./restore-database.sh data-kiosk/backups/platform_backup_20260123_020000.sql.gz
```

**3. Confirmation Required:**
```
⚠️  WARNING: This will OVERWRITE the current database!
Database: platform
Container: platform-db-prod

Are you sure you want to continue? [y/N]:
```

---

## Backup Configuration

### Edit `backup-database.sh` for custom settings:

```bash
# Number of backups to keep
MAX_BACKUPS=4  # Default: 4 (1 month if weekly)

# Backup directory
BACKUP_DIR="data-kiosk/backups"

# Container name
CONTAINER_NAME="platform-db-prod"
```

### mysqldump Flags Used:

| Flag | Purpose |
|------|---------|
| `--single-transaction` | Consistent snapshot without locking tables |
| `--routines` | Include stored procedures |
| `--triggers` | Include triggers |
| `--events` | Include scheduled events |
| `--quick` | Retrieve rows one at a time (memory efficient) |
| `--lock-tables=false` | No table locking (for InnoDB) |

---

## Backup Storage Management

### Current Setup
- **Location:** `data-kiosk/backups/`
- **Format:** `platform_backup_YYYYMMDD_HHMMSS.sql.gz`
- **Retention:** 4 backups (auto-cleanup)
- **Compression:** gzip (~75% reduction)

### Disk Space Example

| Backup | Uncompressed | Compressed | Ratio |
|--------|--------------|------------|-------|
| Week 1 | 148 MB | 37 MB | 75% |
| Week 2 | 152 MB | 38 MB | 75% |
| Week 3 | 155 MB | 39 MB | 75% |
| Week 4 | 158 MB | 40 MB | 75% |
| **Total** | **613 MB** | **154 MB** | **75%** |

### Check Disk Space
```bash
# Backup directory size
du -sh data-kiosk/backups/

# Individual backup sizes
ls -lh data-kiosk/backups/
```

---

## Monitoring & Logs

### Backup Log
```bash
# View backup history
cat logs/backup.log

# Tail log (live)
tail -f logs/backup.log

# Last 10 backups
tail -n 20 logs/backup.log
```

### Log Format
```
2026-01-23 02:00:00 - Backup completed: platform_backup_20260123_020000.sql.gz (37M)
2026-01-16 02:00:00 - Backup completed: platform_backup_20260116_020000.sql.gz (36M)
```

### Email Notifications (Optional)

Add to crontab for email alerts:
```cron
MAILTO=admin@example.com
0 2 * * 0 /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1
```

Or use custom notification in script:
```bash
# Add to backup-database.sh after successful backup
echo "Backup completed: $(basename "$BACKUP_COMPRESSED")" | mail -s "Database Backup Success" admin@example.com
```

---

## Troubleshooting

### Issue: Backup fails with "Container not running"

**Solution:**
```bash
# Check container status
docker ps | grep platform-db-prod

# Start container if needed
cd /home/ubuntu/kiosk
docker compose -f docker-compose.prod.yml up -d mariadb
```

### Issue: Permission denied

**Solution:**
```bash
# Make scripts executable
chmod +x /home/ubuntu/kiosk/backup-database.sh
chmod +x /home/ubuntu/kiosk/restore-database.sh

# Fix backup directory permissions
sudo chown -R ubuntu:ubuntu /home/ubuntu/kiosk/data-kiosk/backups/
```

### Issue: Disk space full

**Solution:**
```bash
# Check disk space
df -h

# Reduce MAX_BACKUPS in backup-database.sh
nano backup-database.sh
# Change: MAX_BACKUPS=4 to MAX_BACKUPS=2

# Manual cleanup old backups
rm data-kiosk/backups/platform_backup_OLDER_*.sql.gz
```

### Issue: Backup taking too long

**Solution:**
```bash
# Check database size
docker compose -f docker-compose.prod.yml exec mariadb mysql -uroot -p"${DB_ROOT_PASSWORD}" -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = 'platform';"

# Consider:
# - Compress during backup (already done with gzip)
# - Exclude large tables if not critical
# - Run backup during low traffic hours
```

---

## Deployment Without Backup

Deploy scripts now **skip** backup step:

**Before:**
```bash
./deploy-prod.sh
# 📦 Creating database backup before deployment...  ← Removed
# 🔨 Building all microservices...
```

**After:**
```bash
./deploy-prod.sh
# ✅ platform.sql found
# 📥 Pulling official base images...
# 🔨 Building all microservices...  ← Direct to build
```

### Manual Backup Before Critical Deploys

If you want backup before specific deploy:
```bash
# 1. Backup first
./backup-database.sh

# 2. Then deploy
./deploy-prod.sh
```

---

## Best Practices

### 1. **Test Restores Regularly**
```bash
# Quarterly restore test to verify backups work
./restore-database.sh data-kiosk/backups/latest_backup.sql.gz
```

### 2. **Off-site Backup** (Recommended)
```bash
# Sync backups to remote storage
rsync -avz data-kiosk/backups/ user@backup-server:/backups/kiosk/

# Or use cloud storage
rclone sync data-kiosk/backups/ remote:kiosk-backups/
```

### 3. **Monitor Backup Success**
```bash
# Add to monitoring script
if ! grep -q "Backup completed" logs/backup.log | tail -1; then
    echo "Backup failed!" | mail -s "Backup Alert" admin@example.com
fi
```

### 4. **Document Recovery Process**
Keep this guide accessible for disaster recovery.

---

## Summary

✅ **Automated:** Weekly backup via cron  
✅ **Compressed:** 75% size reduction with gzip  
✅ **Retention:** Auto-cleanup keeps last 4 backups  
✅ **Separation:** Deploy no longer does backup  
✅ **Logging:** All backups logged to `logs/backup.log`  
✅ **Restore:** Simple one-command restore  

**Backup Command:** `./backup-database.sh`  
**Restore Command:** `./restore-database.sh <file.sql.gz>`  
**Schedule:** Every Sunday 2:00 AM (customizable)
