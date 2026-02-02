# 📋 Post-Deployment Checklist untuk Laravel di Docker

Setelah update image dan recreate container dengan image baru, ikuti langkah-langkah ini:

## 🔄 LANGKAH WAJIB (Urutan Penting!)

### 1️⃣ Clear All Compiled Caches
```bash
# Clear compiled services, routes, config
docker exec cosmic-app-1-prod php artisan clear-compiled
docker exec cosmic-app-2-prod php artisan clear-compiled
docker exec cosmic-app-3-prod php artisan clear-compiled
```

**Kenapa?** Compiled cache berisi snapshot class/route/config lama. Harus di-clear dulu sebelum yang lain.

---

### 2️⃣ Clear Application Caches
```bash
# Clear config cache
docker exec cosmic-app-1-prod php artisan config:clear
docker exec cosmic-app-2-prod php artisan config:clear
docker exec cosmic-app-3-prod php artisan config:clear

# Clear route cache
docker exec cosmic-app-1-prod php artisan route:clear
docker exec cosmic-app-2-prod php artisan route:clear
docker exec cosmic-app-3-prod php artisan route:clear

# Clear view cache
docker exec cosmic-app-1-prod php artisan view:clear
docker exec cosmic-app-2-prod php artisan view:clear
docker exec cosmic-app-3-prod php artisan view:clear

# Clear application cache (Redis/File cache)
docker exec cosmic-app-1-prod php artisan cache:clear
docker exec cosmic-app-2-prod php artisan cache:clear
docker exec cosmic-app-3-prod php artisan cache:clear
```

**Kenapa?** Cache lama bisa contain reference ke code/class yang sudah berubah.

---

### 3️⃣ Run Database Migrations (Jika Ada)
```bash
# HANYA di 1 container! Jangan di semua!
docker exec cosmic-app-1-prod php artisan migrate --force
```

**Kenapa?** Database structure mungkin berubah. `--force` untuk skip konfirmasi di production.

---

### 4️⃣ Restart Containers (Clear OPcache)
```bash
# Restart semua app containers
docker restart cosmic-app-1-prod cosmic-app-2-prod cosmic-app-3-prod

# Restart scheduler juga
docker restart cosmic-scheduler-prod

# Wait untuk containers ready
sleep 10
```

**Kenapa?** PHP OPcache menyimpan compiled PHP code di memory. Restart adalah cara paling aman untuk clear OPcache completely.

---

### 5️⃣ Rebuild Optimized Caches (Warm Up)
```bash
# Rebuild config cache
docker exec cosmic-app-1-prod php artisan config:cache
docker exec cosmic-app-2-prod php artisan config:cache
docker exec cosmic-app-3-prod php artisan config:cache

# Rebuild route cache
docker exec cosmic-app-1-prod php artisan route:cache
docker exec cosmic-app-2-prod php artisan route:cache
docker exec cosmic-app-3-prod php artisan route:cache

# Rebuild view cache
docker exec cosmic-app-1-prod php artisan view:cache
docker exec cosmic-app-2-prod php artisan view:cache
docker exec cosmic-app-3-prod php artisan view:cache

# ATAU gunakan optimize (all-in-one)
docker exec cosmic-app-1-prod php artisan optimize
docker exec cosmic-app-2-prod php artisan optimize
docker exec cosmic-app-3-prod php artisan optimize
```

**Kenapa?** Rebuild cache dengan code baru = performance optimal. `php artisan optimize` = shortcut untuk config:cache + route:cache.

---

### 6️⃣ Verify Deployment
```bash
# Check container health
docker ps | grep cosmic

# Check migration status
docker exec cosmic-app-1-prod php artisan migrate:status

# Check artisan commands available
docker exec cosmic-app-1-prod php artisan list | grep -E "devices|schedule"

# Test response time
curl -s -o /dev/null -w "Response time: %{time_total}s\n" https://kiosk.mugshot.dev/api/health

# Check logs for errors
docker exec cosmic-app-1-prod tail -50 /var/www/storage/logs/laravel.log | grep ERROR
```

---

## 🚀 ONE-LINER SCRIPT

Untuk automated deployment, copy semua langkah di atas:

```bash
#!/bin/bash
echo "🔄 Post-Deployment Tasks..."

# 1. Clear compiled
for c in cosmic-app-{1,2,3}-prod; do docker exec $c php artisan clear-compiled; done

# 2. Clear caches
for c in cosmic-app-{1,2,3}-prod; do 
  docker exec $c php artisan config:clear
  docker exec $c php artisan route:clear
  docker exec $c php artisan view:clear
  docker exec $c php artisan cache:clear
done

# 3. Run migrations (only once)
docker exec cosmic-app-1-prod php artisan migrate --force

# 4. Restart containers (clear OPcache)
docker restart cosmic-app-1-prod cosmic-app-2-prod cosmic-app-3-prod cosmic-scheduler-prod
sleep 10

# 5. Warm up caches
for c in cosmic-app-{1,2,3}-prod; do 
  docker exec $c php artisan optimize
done

# 6. Verify
docker ps | grep cosmic
docker exec cosmic-app-1-prod php artisan migrate:status

echo "✅ Post-Deployment Complete!"
```

---

## ⚠️ COMMON MISTAKES

### ❌ Jangan Lakukan Ini:
1. **Skip clear-compiled** → Compiled cache masih reference code lama
2. **Skip restart containers** → OPcache masih cache PHP code lama
3. **Optimize sebelum clear** → Cache PHP lama, bukan yang baru
4. **Run migration di semua container** → Race condition, bisa corrupt database
5. **Skip verification** → Tidak tahu kalau ada error

### ✅ Urutan yang Benar:
```
Clear Compiled → Clear Caches → Migrate → Restart → Optimize → Verify
```

---

## 🎯 KENAPA URUTAN PENTING?

1. **Clear Compiled dulu** karena ini cache "meta" yang reference cache lain
2. **Clear Caches** sebelum migrate agar tidak ada stale references
3. **Migrate** sebelum restart agar schema ready
4. **Restart** untuk clear OPcache (memory-level cache)
5. **Optimize** terakhir untuk rebuild cache dengan code baru
6. **Verify** untuk pastikan semua OK

---

## 📊 TROUBLESHOOTING

### Jika masih lambat setelah deployment:
```bash
# 1. Check OPcache masih ada?
docker exec cosmic-app-1-prod php -i | grep opcache

# 2. Force clear OPcache dengan restart
docker restart cosmic-app-1-prod cosmic-app-2-prod cosmic-app-3-prod

# 3. Clear Redis cache manually
docker exec platform-redis-prod redis-cli FLUSHDB

# 4. Check logs
docker logs cosmic-app-1-prod | tail -100
```

### Jika ada error setelah deployment:
```bash
# Check Laravel logs
docker exec cosmic-app-1-prod tail -100 /var/www/storage/logs/laravel.log

# Check container logs
docker logs cosmic-app-1-prod --tail 100

# Check permissions
docker exec cosmic-app-1-prod ls -la /var/www/storage/logs/
docker exec cosmic-app-1-prod ls -la /var/www/bootstrap/cache/
```

---

## 📝 NOTES

- **Development**: Tidak perlu semua langkah ini, cukup `php artisan optimize:clear`
- **Production**: HARUS ikuti semua langkah untuk stability
- **Zero-downtime**: Rolling restart (restart 1-1 container) kalau traffic tinggi
- **Monitoring**: Watch logs selama 5-10 menit setelah deployment

---

**Created**: 2 February 2026  
**Last Updated**: 2 February 2026  
**Environment**: Docker Compose Production
