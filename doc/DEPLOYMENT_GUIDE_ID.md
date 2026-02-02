# 🚀 Panduan Deployment - Backend Fixes untuk Docker Production

**Tanggal**: 2 Februari 2026  
**Status**: ✅ Siap Deploy  
**Lingkungan**: Docker Production (docker-compose.prod.yml)  

---

## 📋 RINGKASAN IMPLEMENTASI

Semua 7 perbaikan backend untuk sistem konektivitas device telah **SELESAI DIIMPLEMENTASIKAN**:

1. ✅ **Heartbeat Enforcement** - Timeout dikelola server dengan 3 tingkat status
2. ✅ **Status Ownership** - CMS sebagai sumber otoritas utama
3. ✅ **Atomic State Updates** - Row-level locking mencegah race condition
4. ✅ **Cache Invalidation Control** - Scope per-device, tidak ada cache thrashing
5. ✅ **Heartbeat Rate Limiting** - Mencegah abuse
6. ✅ **Server-Initiated Signaling** - Mekanisme reconnection berfungsi
7. ✅ **Observability** - Logging terstruktur lengkap

---

## 🎯 PERSIAPAN DEPLOYMENT

### Step 1: Cek Kesiapan Sistem

```bash
cd /home/ubuntu/kiosk
bash check_deployment_ready.sh
```

**Output yang diharapkan:**
```
✓ PASS: Docker is running
✓ PASS: All containers are running
✓ PASS: Database connection working
✓ PASS: All files exist
⚠ WARN: 1 pending migration (normal)
```

---

## 🚀 DEPLOYMENT OTOMATIS (DISARANKAN)

### Jalankan Script Deployment

```bash
cd /home/ubuntu/kiosk
bash deploy_backend_fixes_docker.sh
```

Script akan otomatis:
1. ✅ Backup database ke `data-kiosk/backups/`
2. ✅ Rebuild Docker images (cosmic-app)
3. ✅ Jalankan migration
4. ✅ Clear semua cache
5. ✅ Restart services (zero-downtime)
6. ✅ Verifikasi deployment

**Durasi:** Sekitar 5-10 menit

---

## 📊 MONITORING SETELAH DEPLOYMENT

### 1. Monitor Status Transitions

```bash
# Real-time monitoring
docker exec -it cosmic-app-1-prod tail -f /var/www/storage/logs/laravel.log \
  | grep "Device status"
```

**Yang diharapkan:**
```json
{
  "device_id": 123,
  "device_name": "KIOSK-LOBBY-01",
  "from_status": "Connected",
  "to_status": "Temporarily Offline",
  "reason": "No heartbeat for 45s (grace period: 60s)",
  "source": "system"
}
```

### 2. Cek Scheduler Berjalan

```bash
# Lihat log scheduler
docker logs -f cosmic-scheduler-prod
```

**Setiap menit akan muncul:**
```
Running scheduled command: Artisan devices:monitor-status
```

### 3. Test Heartbeat Manual

```bash
# Ganti YOUR_DEVICE_TOKEN dengan token device
curl -X POST http://localhost:8080/api/devices/heartbeat \
  -H "Authorization: Bearer YOUR_DEVICE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"battery_level": 85, "wifi_strength": -45}'
```

**Response yang diharapkan:**
```json
{
  "success": true,
  "data": {
    "remote_control_enabled": true,
    "should_reconnect": false,
    "reconnect_delay_seconds": null
  }
}
```

### 4. Cek Database Changes

```bash
# Masuk ke MySQL
docker exec -it platform-db-prod mysql -u root -p platform

# Lihat kolom baru
DESCRIBE remotes;

# Lihat status changes terbaru
SELECT id, name, status, previous_status, 
       last_status_change_at, status_change_reason
FROM remotes 
WHERE last_status_change_at > NOW() - INTERVAL 1 HOUR
ORDER BY last_status_change_at DESC
LIMIT 10;
```

---

## 🔧 COMMAND PENTING

### Artisan Commands (Dalam Container)

```bash
# Jalankan status monitor manual
docker exec cosmic-app-1-prod php artisan devices:monitor-status --verbose

# Dry-run (tidak ubah data, hanya simulasi)
docker exec cosmic-app-1-prod php artisan devices:monitor-status --dry-run --verbose

# Cek migration status
docker exec cosmic-app-1-prod php artisan migrate:status

# Cek scheduled tasks
docker exec cosmic-scheduler-prod php artisan schedule:list
```

### Docker Commands

```bash
# Restart app containers (satu per satu untuk zero-downtime)
docker-compose -f docker-compose.prod.yml restart cosmic-app-1
docker-compose -f docker-compose.prod.yml restart cosmic-app-2
docker-compose -f docker-compose.prod.yml restart cosmic-app-3

# Restart scheduler
docker-compose -f docker-compose.prod.yml restart cosmic-scheduler

# Lihat logs
docker logs -f cosmic-app-1-prod
docker logs -f cosmic-scheduler-prod

# Rebuild image jika ada perubahan code
docker-compose -f docker-compose.prod.yml build cosmic-app-1
```

---

## ⚠️ TROUBLESHOOTING

### Masalah: Migration Gagal

```bash
# Cek error
docker logs cosmic-app-1-prod | grep -i error

# Rollback migration
docker exec cosmic-app-1-prod php artisan migrate:rollback --step=1

# Coba lagi
docker exec cosmic-app-1-prod php artisan migrate --force
```

### Masalah: Command Tidak Ditemukan

```bash
# Clear cache
docker exec cosmic-app-1-prod php artisan config:clear

# Cek file ada
docker exec cosmic-app-1-prod ls -la /var/www/app/Console/Commands/

# Rebuild dan restart
docker-compose -f docker-compose.prod.yml build cosmic-app-1
docker-compose -f docker-compose.prod.yml restart cosmic-app-1
```

### Masalah: Scheduler Tidak Jalan

```bash
# Cek scheduler container
docker ps | grep scheduler

# Lihat log detail
docker logs --tail 100 cosmic-scheduler-prod

# Restart scheduler
docker-compose -f docker-compose.prod.yml restart cosmic-scheduler
```

### Masalah: Device Status Flapping

```bash
# Cek transitions yang terlalu sering
docker exec cosmic-app-1-prod php artisan tinker --execute="
DB::table('remotes')
  ->where('last_status_change_at', '>', now()->subMinutes(10))
  ->select('name', 'status', 'previous_status', 'status_change_reason')
  ->get()
"

# Tingkatkan grace period untuk device tertentu
docker exec cosmic-app-1-prod php artisan tinker --execute="
DB::table('remotes')->where('id', 123)->update([
  'grace_period_seconds' => 120
])
"
```

---

## 🔄 ROLLBACK (Jika Ada Masalah)

### 1. Rollback Migration

```bash
docker exec cosmic-app-1-prod php artisan migrate:rollback --step=1
```

### 2. Restore Database

```bash
# List backup files
ls -lt data-kiosk/backups/

# Restore dari backup
docker exec -i platform-db-prod mysql -u root -p platform \
  < data-kiosk/backups/pre-migration-YYYYMMDD_HHMMSS.sql
```

### 3. Revert Code (Jika Perlu)

```bash
cd cosmic-media-streaming-dpr
git stash  # Simpan changes
git log --oneline -10  # Lihat commits
git checkout <commit-sebelumnya>

# Rebuild
cd ..
docker-compose -f docker-compose.prod.yml build cosmic-app-1 cosmic-app-2 cosmic-app-3
docker-compose -f docker-compose.prod.yml restart cosmic-app-1 cosmic-app-2 cosmic-app-3
```

---

## 📈 CHECKLIST SETELAH DEPLOYMENT

**Hari 1 (24 jam pertama):**
- [ ] Migration sukses tanpa error
- [ ] Semua container healthy
- [ ] Scheduler running setiap menit
- [ ] Device status transitions tercatat di log
- [ ] Tidak ada status flapping yang berlebihan
- [ ] Heartbeat response termasuk field baru
- [ ] Rate limiting berfungsi (cek 429 responses)
- [ ] Cache tidak di-flush global setiap heartbeat

**Hari 2-7 (Monitoring):**
- [ ] Database performance normal
- [ ] Tidak ada peningkatan error rate
- [ ] Device connectivity lebih stabil
- [ ] Log menunjukkan reason transitions yang jelas
- [ ] External service (Python) terkoordinasi

---

## 📂 FILE-FILE PENTING

### Script Deployment
```
deploy_backend_fixes_docker.sh     → Main deployment script
check_deployment_ready.sh          → Pre-deployment check
```

### Dokumentasi
```
DOCKER_DEPLOYMENT_GUIDE.md         → Panduan deployment Docker (lengkap)
IMPLEMENTATION_BACKEND_FIXES.md    → Dokumentasi teknis lengkap
QUICK_REFERENCE.md                 → Reference cepat
ARCHITECTURE_DIAGRAMS.md           → Diagram arsitektur
IMPLEMENTATION_COMPLETE.md         → Summary implementasi
```

### Logs
```
data-kiosk/logs/cosmic-app-1/laravel.log    → App logs (instance 1)
data-kiosk/logs/cosmic-app-2/laravel.log    → App logs (instance 2)
data-kiosk/logs/cosmic-app-3/laravel.log    → App logs (instance 3)
data-kiosk/logs/cosmic-scheduler/           → Scheduler logs
```

---

## 🎯 LANGKAH DEPLOYMENT LENGKAP

### Persiapan (5 menit)

```bash
cd /home/ubuntu/kiosk

# 1. Cek sistem siap
bash check_deployment_ready.sh

# 2. Backup manual (opsional, script juga backup otomatis)
docker exec platform-db-prod mysqldump -u root -p${DB_ROOT_PASSWORD} platform \
  > data-kiosk/backups/manual-backup-$(date +%Y%m%d_%H%M%S).sql
```

### Deployment (5-10 menit)

```bash
# 3. Jalankan deployment
bash deploy_backend_fixes_docker.sh

# Script akan handle semua step:
# - Backup database
# - Rebuild images
# - Run migration
# - Clear cache
# - Restart services
# - Verify deployment
```

### Verifikasi (10 menit)

```bash
# 4. Cek container health
docker ps | grep cosmic

# 5. Cek migration sukses
docker exec cosmic-app-1-prod php artisan migrate:status

# 6. Test command
docker exec cosmic-app-1-prod php artisan devices:monitor-status --dry-run --verbose

# 7. Monitor logs
docker logs -f cosmic-scheduler-prod &
docker exec -it cosmic-app-1-prod tail -f /var/www/storage/logs/laravel.log | grep "Device status" &
```

### Monitoring (24 jam pertama)

```bash
# 8. Watch untuk issues
# - Status transitions normal?
# - Tidak ada error berulang?
# - Scheduler running setiap menit?
# - Database load normal?
```

---

## 💡 TIPS PENTING

1. **Zero-Downtime**: Dengan 3 replicas (cosmic-app-1/2/3), restart satu per satu dengan jeda 10-15 detik
2. **Backup**: Script otomatis backup sebelum migration, tapi bisa backup manual juga
3. **Monitoring**: Pantau logs minimal 1 jam setelah deployment
4. **Rollback**: Jika ada masalah serius, rollback segera jangan tunggu
5. **External Service**: Update Python service untuk gunakan API baru (opsional tapi disarankan)

---

## ✅ SIAP DEPLOY

Semua file sudah ada dan siap:
- ✅ Migration file created
- ✅ Service classes implemented  
- ✅ Middleware registered
- ✅ Commands created
- ✅ Scheduler configured
- ✅ Documentation complete
- ✅ Scripts ready

**Untuk memulai deployment:**

```bash
cd /home/ubuntu/kiosk
bash deploy_backend_fixes_docker.sh
```

---

## 📞 BANTUAN

Jika ada masalah saat deployment:

1. **Cek logs**: `docker logs cosmic-app-1-prod | tail -100`
2. **Lihat dokumentasi lengkap**: `DOCKER_DEPLOYMENT_GUIDE.md`
3. **Rollback jika perlu**: Ikuti prosedur rollback di atas

**Semua implementasi sudah sesuai dengan analisis dan requirements!** 🎉
