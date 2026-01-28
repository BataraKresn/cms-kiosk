# ✅ Deployment Checklist

## Pre-Deployment Checklist

### Development Environment

- [ ] Docker & Docker Compose terinstall (versi terbaru)
- [ ] Clone/download project ke server
- [ ] Copy `.env.example` ke `.env`
- [ ] Review konfigurasi di `.env` (optional untuk dev)
- [ ] Pastikan port tidak bentrok (3306, 6379, 8000, 3333, 3001, 8080, 8081, 9000, 9001)
- [ ] Jalankan `./deploy-dev.sh`
- [ ] Verifikasi semua services running: `docker compose -f docker-compose.dev.yml ps`
- [ ] Test akses ke http://localhost:8000
- [ ] Test akses ke http://localhost:8080 (phpMyAdmin)

### Production Environment

#### Security
- [ ] Copy `.env.example` ke `.env`
- [ ] **GANTI semua password default:**
  - [ ] `DB_ROOT_PASSWORD`
  - [ ] `DB_PASSWORD`
  - [ ] `MINIO_SECRET`
- [ ] Set `APP_DEBUG=false` di cosmic-media-streaming-dpr/.env
- [ ] Set `APP_ENV=production` di cosmic-media-streaming-dpr/.env
- [ ] Generate `APP_KEY` yang baru
- [ ] Review semua environment variables

#### Infrastructure
- [ ] Server specifications adequate (min: 4GB RAM, 4 CPU cores, 100GB storage)
- [ ] Firewall configured
- [ ] Ports exposed sesuai kebutuhan
- [ ] Domain/subdomain configured (optional)
- [ ] SSL certificates ready (if using HTTPS)
- [ ] Backup storage configured

#### Deployment
- [ ] Jalankan `./deploy-prod.sh`
- [ ] Verifikasi semua services running
- [ ] Test database connection
- [ ] Test inter-service communication
- [ ] Run smoke tests
- [ ] Configure Nginx reverse proxy (optional tapi recommended)

#### Post-Deployment
- [ ] Setup monitoring (Prometheus + Grafana)
- [ ] Configure log aggregation (ELK atau Loki)
- [ ] Setup automated backups (database, files)
- [ ] Document any customizations
- [ ] Create disaster recovery plan
- [ ] Test backup restoration procedure
- [ ] Configure alerts
- [ ] Load testing
- [ ] Security audit

---

## Daily Operations Checklist

### Morning Check
- [ ] Check service status: `docker compose ps`
- [ ] Review logs for errors: `docker compose logs --since 24h`
- [ ] Check disk space: `df -h`
- [ ] Check database size
- [ ] Verify backups completed successfully

### Weekly Check
- [ ] Review performance metrics
- [ ] Check for Docker image updates
- [ ] Review security alerts
- [ ] Test restore from backup
- [ ] Update documentation if needed

### Monthly Check
- [ ] Security updates for all services
- [ ] Review and optimize database
- [ ] Clean up old logs and backups
- [ ] Review resource usage and scaling needs
- [ ] Performance tuning if needed

---

## Troubleshooting Checklist

### Service Won't Start
- [ ] Check logs: `docker compose logs [service]`
- [ ] Verify .env file exists and is correct
- [ ] Check port conflicts: `sudo lsof -i :[port]`
- [ ] Verify Docker daemon running
- [ ] Check disk space
- [ ] Try rebuilding: `docker compose build --no-cache [service]`

### Database Issues
- [ ] Check MariaDB logs: `docker compose logs mariadb`
- [ ] Verify credentials in .env
- [ ] Test connection: `docker compose exec mariadb mysql -u[user] -p`
- [ ] Check if database imported: Login to phpMyAdmin
- [ ] Verify restore.sql ran successfully

### Performance Issues
- [ ] Check resource usage: `docker stats`
- [ ] Review slow query log (MariaDB)
- [ ] Check Redis memory: `docker compose exec redis redis-cli INFO memory`
- [ ] Review Laravel queue backlog
- [ ] Check disk I/O

---

## Emergency Procedures

### Service Down
1. Check status: `docker compose ps`
2. View logs: `docker compose logs [service]`
3. Attempt restart: `docker compose restart [service]`
4. If fails, check: disk space, memory, logs
5. Last resort: Full restart: `docker compose down && docker compose up -d`

### Database Corruption
1. Stop all services: `docker compose down`
2. Restore from latest backup
3. Verify restoration
4. Start services: `docker compose up -d`
5. Run migrations if needed
6. Verify data integrity

### Complete System Failure
1. Document current state (screenshots, logs)
2. Stop all services: `docker compose down`
3. Backup current data (if possible)
4. Fresh deployment: `./deploy-prod.sh`
5. Restore database from backup
6. Restore files from backup
7. Verify all services
8. Post-mortem analysis

---

## Maintenance Windows

### Minor Updates (30 minutes)
- [ ] Announce maintenance window
- [ ] Create backup
- [ ] Pull latest code/images
- [ ] Run `./deploy-prod.sh`
- [ ] Verify services
- [ ] Monitor for issues

### Major Updates (1-2 hours)
- [ ] Full system backup
- [ ] Test in staging first
- [ ] Announce extended maintenance
- [ ] Deploy updates
- [ ] Run full test suite
- [ ] Monitor for 24 hours
- [ ] Document changes

---

## Rollback Procedure

### Quick Rollback
```bash
# Stop current version
docker compose down

# Restore previous version (if using git)
git checkout [previous-commit]

# Deploy
./deploy-prod.sh

# Restore database if needed
docker compose exec -T mariadb mysql -uroot -p platform < backups/backup_YYYYMMDD.sql
```

### Full Rollback
1. Stop all services
2. Restore code from backup
3. Restore database from backup
4. Restore files from backup
5. Deploy previous version
6. Verify everything working
7. Document what went wrong

---

## Success Metrics

### After Development Deployment
- [ ] All services show "Up" status
- [ ] Can login to Cosmic Media
- [ ] Can access phpMyAdmin
- [ ] Can create/edit content
- [ ] Queue processing working
- [ ] Scheduler running

### After Production Deployment
- [ ] All services healthy
- [ ] Response time < 200ms (average)
- [ ] No errors in logs
- [ ] Database queries optimized
- [ ] Backups running successfully
- [ ] Monitoring active
- [ ] Alerts configured
- [ ] SSL working (if configured)

---

## Contact & Escalation

### Internal Team
- DevOps Lead: [Name/Contact]
- Backend Developer: [Name/Contact]
- Database Admin: [Name/Contact]

### External Support
- Docker Support: https://docs.docker.com/
- Laravel Support: https://laravel.com/docs
- MariaDB Support: https://mariadb.com/kb/

### Emergency Contacts
- On-call DevOps: [Phone]
- System Admin: [Phone]
- CTO/Tech Lead: [Phone]

---

**Last Updated:** January 22, 2026  
**Version:** 1.0.0
