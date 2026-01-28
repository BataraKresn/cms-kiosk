# 🚀 Deployment Guide

## Quick Commands

### View Available Options
```bash
./deploy-prod.sh --help
```

### Normal Deployment
```bash
./deploy-prod.sh
```

### Deployment with Database Backup (Recommended)
```bash
./deploy-prod.sh --backup
# or
./deploy-prod.sh -b
```

### Force Rebuild + Backup
```bash
./deploy-prod.sh --force-rebuild --backup
# or
./deploy-prod.sh -f -b
```

### Stop Services, Backup, Then Deploy
```bash
./deploy-prod.sh --stop --backup
# or
./deploy-prod.sh -s -b
```

---

## 📦 Database Backup

### Manual Backup
```bash
./backup-database.sh
```

### Automated Backup (Cron)
```bash
# Edit crontab
crontab -e

# Add this line for weekly backup (every Sunday at 2 AM)
0 2 * * 0  /home/ubuntu/kiosk/backup-database.sh >> /home/ubuntu/kiosk/logs/backup.log 2>&1
```

### Backup Location
- **Directory:** `data-kiosk/backups/`
- **Format:** `platform_backup_YYYYMMDD_HHMMSS.sql.gz`
- **Retention:** Last 4 backups (auto-cleanup)
- **Compression:** gzip

### List Backups
```bash
ls -lh data-kiosk/backups/*.gz
```

### Restore Backup
```bash
# See restore-database.sh for restore instructions
./restore-database.sh
```

---

## 🎯 Deployment Options

| Option | Short | Description |
|--------|-------|-------------|
| `--help` | `-h` | Show help message |
| `--force-rebuild` | `-f` | Force rebuild all images (no cache) |
| `--stop` | `-s` | Stop all services before deployment |
| `--backup` | `-b` | Backup database before deployment |

---

## ✅ Best Practices

### Before Production Deployment
1. ✅ Always backup: `./deploy-prod.sh -b`
2. ✅ Test in dev environment first
3. ✅ Check `.env.prod` configuration
4. ✅ Verify no sensitive data in logs

### After Deployment
1. ✅ Check container health: `docker ps`
2. ✅ Test critical endpoints
3. ✅ Monitor logs: `docker compose logs -f`
4. ✅ Verify backup created: `ls -lh data-kiosk/backups/`

### Regular Maintenance
1. ✅ Weekly automated backups via cron
2. ✅ Monthly cleanup of old logs
3. ✅ Monitor disk space: `df -h`
4. ✅ Review backup integrity

---

## 🔧 Troubleshooting

### Deployment Failed
```bash
# Check logs
docker compose -f docker-compose.prod.yml logs --tail=100

# Check specific service
docker logs cosmic-app-1-prod --tail=50
```

### Backup Failed
```bash
# Check backup logs
tail -50 logs/backup.log

# Verify database container running
docker ps | grep platform-db-prod

# Test database connection
docker compose -f docker-compose.prod.yml exec mariadb mysql -uroot -p -e "SELECT 1"
```

### Restore From Backup
```bash
# List available backups
ls -lh data-kiosk/backups/*.gz

# Restore specific backup
gunzip -c data-kiosk/backups/platform_backup_YYYYMMDD_HHMMSS.sql.gz | \
docker compose -f docker-compose.prod.yml exec -T mariadb mysql -uroot -p platform
```

---

## 📊 Performance Tips

### Don't Mount These in Production
❌ **Never mount:** `app/`, `routes/`, `config/` directories  
✅ **OK to mount:** `database/migrations/` (for convenience)

**Reason:** Volume mounts cause 10-14x slower performance (14s vs 1s page load)

### If You Need to Update Code
```bash
# 1. Update code in repository
git pull

# 2. Rebuild containers (includes new code)
./deploy-prod.sh -f

# 3. Don't add volume mounts for code files
```

---

## 📝 Example Workflows

### Routine Deployment
```bash
cd /home/ubuntu/kiosk
./deploy-prod.sh -b    # Deploy with backup
```

### Emergency Rebuild
```bash
cd /home/ubuntu/kiosk
./deploy-prod.sh -f -b  # Force rebuild with backup
```

### Major Update Deployment
```bash
cd /home/ubuntu/kiosk

# 1. Stop services
./deploy-prod.sh -s -b

# 2. Verify deployment
docker ps
docker compose logs -f

# 3. Test endpoints
curl https://kiosk.mugshot.dev/api/health
```

---

## 🆘 Emergency Contacts

**If deployment fails:**
1. Check Docker logs
2. Verify `.env.prod` settings
3. Restore from backup if needed
4. Contact DevOps team

**Backup verification:**
```bash
# Should show recent backups
ls -lht data-kiosk/backups/ | head -5
```
