# Redis & Queue Setup Guide

## 📦 Installing Redis on Windows

### Option 1: WSL2 (Recommended)

1. **Install WSL2:**
```powershell
# Run as Administrator
wsl --install
# Restart computer
```

2. **Install Redis in WSL:**
```bash
# Update package list
sudo apt update

# Install Redis
sudo apt install redis-server -y

# Start Redis
sudo service redis-server start

# Test Redis
redis-cli ping
# Should return: PONG
```

3. **Auto-start Redis:**
```bash
# Edit sudoers to allow service start without password
echo "$USER ALL=(ALL) NOPASSWD: /usr/sbin/service redis-server start" | sudo tee /etc/sudoers.d/redis

# Add to ~/.bashrc
echo "sudo service redis-server start" >> ~/.bashrc
```

### Option 2: Native Windows (Alternative)

Download dari: https://github.com/microsoftarchive/redis/releases
- Install MSI package
- Redis akan berjalan sebagai Windows Service

---

## 🔧 Laravel Configuration

### 1. Update .env
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Install Predis (if not using phpredis extension)
```bash
composer require predis/predis
```

### 3. Clear Config Cache
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🚀 Running Queue Worker

### Development

**PowerShell:**
```powershell
# Simple worker
php artisan queue:work

# With options
php artisan queue:work redis --tries=3 --timeout=90 --sleep=3

# Watch for code changes (restart automatically)
php artisan queue:work --tries=3 --max-time=3600
```

**Tips:**
- Gunakan separate terminal window untuk queue worker
- Restart worker setiap kali ada code changes
- Monitor logs: `tail -f storage/logs/laravel.log`

### Production (dengan Supervisor)

#### Install Supervisor (Linux):
```bash
sudo apt install supervisor
```

#### Create Config File:
File: `/etc/supervisor/conf.d/cosmic-media-queue.conf`

```ini
[program:cosmic-media-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

#### Manage Supervisor:
```bash
# Reload config
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start cosmic-media-queue:*

# Check status
sudo supervisorctl status

# Stop workers
sudo supervisorctl stop cosmic-media-queue:*

# Restart workers
sudo supervisorctl restart cosmic-media-queue:*
```

---

## 🔍 Monitoring Queue

### Artisan Commands:

```bash
# List all queued jobs
php artisan queue:work --once

# Show failed jobs
php artisan queue:failed

# Retry specific failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush

# Listen and process jobs
php artisan queue:listen
```

### Queue Monitoring Dashboard (Optional):

**Install Laravel Horizon:**
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

**Config:** `config/horizon.php`

**Run Horizon:**
```bash
php artisan horizon
```

**Access Dashboard:**
```
http://localhost/horizon
```

---

## 📊 Testing Queue System

### Test RefreshDisplayJob:

**PHP Tinker:**
```bash
php artisan tinker
```

```php
// Dispatch a test job
App\Jobs\RefreshDisplayJob::dispatch('test-token-123', 'http://127.0.0.1:3000');

// Check job was queued
DB::table('jobs')->count();

// Check if job was processed (should be 0 after processing)
DB::table('jobs')->count();

// Check failed jobs
DB::table('failed_jobs')->count();
```

### Test API Endpoint:

```bash
# Using curl
curl -X POST http://localhost/api/refresh_display \
  -H "Content-Type: application/json" \
  -d '{"video_id": 1}'

# Using PowerShell
Invoke-RestMethod -Uri "http://localhost/api/refresh_display" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"video_id": 1}'
```

---

## 🐛 Troubleshooting

### Redis Connection Failed

**Check Redis is running:**
```bash
redis-cli ping
```

**Check Laravel can connect:**
```bash
php artisan tinker
Redis::ping();
```

**Common Issues:**
- Redis not started: `sudo service redis-server start`
- Wrong host/port in .env
- PHP Redis extension not installed
- Firewall blocking port 6379

### Queue Not Processing

**Check worker is running:**
```powershell
# List processes
Get-Process | Where-Object {$_.ProcessName -like "*php*"}
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Common Issues:**
- Worker not running: Start dengan `php artisan queue:work`
- Code changed: Restart worker
- Failed jobs: Check `php artisan queue:failed`
- Permission issues: Check `storage/` writable

### Jobs Failing Silently

**Enable detailed logging:**

`.env`:
```env
LOG_LEVEL=debug
QUEUE_CONNECTION=redis
```

**Check failed jobs table:**
```bash
php artisan queue:failed-table
php artisan migrate
```

---

## ⚡ Performance Tips

### 1. Multiple Queue Workers

Run multiple workers untuk better throughput:

```bash
# Terminal 1
php artisan queue:work redis --queue=high --tries=3

# Terminal 2
php artisan queue:work redis --queue=default --tries=3

# Terminal 3
php artisan queue:work redis --queue=low --tries=3
```

### 2. Optimize Redis

**redis.conf tweaks:**
```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
save ""  # Disable RDB snapshots untuk pure cache
```

### 3. Queue Priorities

Dispatch jobs dengan priority:

```php
RefreshDisplayJob::dispatch($token, $urlAPI)->onQueue('high');
```

### 4. Job Batching

Untuk process multiple displays:

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new RefreshDisplayJob($token1, $url),
    new RefreshDisplayJob($token2, $url),
    new RefreshDisplayJob($token3, $url),
])->then(function (Batch $batch) {
    // All jobs completed successfully
})->catch(function (Batch $batch, Throwable $e) {
    // First batch job failure
})->finally(function (Batch $batch) {
    // Batch has finished executing
})->dispatch();

return $batch->id;
```

---

## 📈 Scaling Recommendations

### For High Traffic:

1. **Separate Redis instances:**
   - Redis for cache (port 6379)
   - Redis for queue (port 6380)

2. **Use Redis Cluster** untuk horizontal scaling

3. **Queue Workers on separate server:**
   - Dedicated server untuk queue processing
   - Load balancing untuk web servers

4. **Use AWS SQS** atau **RabbitMQ** untuk enterprise

---

## 🔐 Security Considerations

### Production Redis:

1. **Set password:**
```bash
# Edit redis.conf
requirepass your_strong_password_here
```

2. **Update .env:**
```env
REDIS_PASSWORD=your_strong_password_here
```

3. **Bind to localhost only:**
```conf
bind 127.0.0.1
```

4. **Disable dangerous commands:**
```conf
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command KEYS ""
```

---

## ✅ Checklist

- [ ] Redis installed and running
- [ ] Laravel .env configured for Redis
- [ ] Queue worker running in terminal
- [ ] Test job dispatched successfully
- [ ] Failed jobs table created
- [ ] Supervisor configured (production)
- [ ] Monitoring setup (Horizon/logs)
- [ ] Redis password set (production)
- [ ] Backup strategy for Redis data

---

**Need Help?**
- Laravel Queue Docs: https://laravel.com/docs/10.x/queues
- Redis Docs: https://redis.io/docs/
- Laravel Horizon: https://laravel.com/docs/10.x/horizon
