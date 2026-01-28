# Production Performance & Deployment Guide
**Cosmic Media Streaming Platform**

---

## 📊 Performance Metrics

### Current Performance (After Optimization)
- **Homepage Load**: ~250ms ⚡
- **Menu Navigation**: 22-62ms per request
- **Asset Loading**: 
  - Stylesheets: 22-59ms
  - Scripts: 32-59ms
  - Fonts: 22-40ms
  - SVG: 0-62ms

### Performance Improvements Implemented
✅ **OPcache Optimization**
- Memory: 256MB (from 128MB)
- Max files: 20,000 (from 10,000)
- Validate timestamps: OFF (production mode - 10x faster)
- String buffer: 16MB (from 8MB)

✅ **PHP-FPM Tuning**
- Max children: 75 (from 50)
- Start servers: 10 (from 5)
- Max spare: 20 (from 10)
- Max requests: 1000 (from 500)
- Idle timeout: 10s (new)

✅ **Laravel Caching**
- ✅ Config cache
- ✅ Route cache
- ✅ Event cache
- ✅ View cache
- ✅ Filament components cache
- ✅ Navigation cache enabled

✅ **HTTPS Enforcement**
- All URLs forced to HTTPS via `URL::forceScheme('https')`
- Works behind reverse proxy (Cloudflare → NPM → Docker)

---

## 🚀 One-Command Deployment

### Production Deployment Script
File: `/home/ubuntu/kiosk/deploy-prod.sh`

**Features:**
- ✅ Automatic security checks (no default passwords)
- ✅ Git pull latest code
- ✅ Build all Docker services
- ✅ Run database migrations
- ✅ Auto-cache all optimizations
- ✅ Create storage symlinks
- ✅ Health checks

### Usage:
```bash
cd /home/ubuntu/kiosk

# Standard deployment
./deploy-prod.sh

# Force rebuild all images
./deploy-prod.sh --force-rebuild

# Stop all services first, then deploy
./deploy-prod.sh --stop
```

### What Happens Automatically:
1. **Network setup** - Creates kiosk-net if not exists
2. **Security checks** - Validates production credentials
3. **Git pull** - Updates code from repository
4. **Docker build** - Builds all services with optimizations
5. **Service startup** - Starts all containers in order
6. **Health checks** - Waits for services to be healthy
7. **Laravel setup**:
   - Database migrations
   - Storage link creation
8. **Performance optimization**:
   - Composer autoload optimization with APCu
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan event:cache`
   - `php artisan view:cache`
   - `php artisan filament:optimize`
9. **Status report** - Shows all service status

**✅ NO MANUAL STEPS REQUIRED!**

---

## 🗄️ Redis Cache Configuration

### Cache Strategy (All Redis)
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
BROADCAST_DRIVER=redis
```

### Redis Connections
- **Database 0**: Default connection (queue, broadcast)
- **Database 1**: Cache connection
- **Separate connections prevent conflicts**

### Redis Configuration
```yaml
redis:
  maxmemory: 2gb
  maxmemory-policy: allkeys-lru
  appendonly: yes  # Data persistence
  save:
    - 900 1    # Save after 900s if 1 key changed
    - 300 10   # Save after 300s if 10 keys changed
    - 60 10000 # Save after 60s if 10000 keys changed
```

### Current Redis Stats
```bash
# Check Redis performance
docker compose -f docker-compose.prod.yml exec redis redis-cli INFO stats
```

---

## ⚙️ Queue Workers Explained

### cosmic-queue-1-prod & cosmic-queue-2-prod

**Purpose**: Process background jobs asynchronously

**What They Do:**
1. **Video Processing** - FFmpeg encoding jobs
2. **Image Optimization** - Thumbnail generation
3. **PDF Generation** - Report/document creation
4. **Email Sending** - Notification emails
5. **File Uploads** - MinIO S3 uploads
6. **Scheduled Tasks** - Cron-like jobs

**Why 2 Workers?**
- **Parallel Processing** - Handle multiple jobs simultaneously
- **High Availability** - If one fails, other continues
- **Load Distribution** - Better throughput
- **Scalability** - Can add more workers if needed

**Configuration:**
```yaml
command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=600

# Parameters:
# --sleep=3        : Wait 3 seconds when no jobs
# --tries=3        : Retry failed jobs 3 times
# --max-time=3600  : Restart worker after 1 hour (prevent memory leaks)
# --timeout=600    : Job timeout 10 minutes (for long processing)
```

**Monitoring:**
```bash
# Check queue status
docker compose -f docker-compose.prod.yml exec cosmic-app php artisan queue:work --once

# View failed jobs
docker compose -f docker-compose.prod.yml exec cosmic-app php artisan queue:failed

# Retry failed jobs
docker compose -f docker-compose.prod.yml exec cosmic-app php artisan queue:retry all
```

---

## 📁 File Structure (No Container Edits!)

### ✅ All Changes in Source Code
```
/home/ubuntu/kiosk/
├── .env.prod                    # Production environment (EDIT HERE)
├── docker-compose.prod.yml      # Service orchestration (EDIT HERE)
├── deploy-prod.sh               # Deployment script (EDIT HERE)
│
├── cosmic-media-streaming-dpr/
│   ├── Dockerfile               # App container build (EDIT HERE)
│   ├── docker/
│   │   └── nginx/
│   │       └── default.conf     # Nginx config (EDIT HERE)
│   ├── app/
│   │   └── Providers/
│   │       └── AppServiceProvider.php  # HTTPS force (EDIT HERE)
│   └── config/                  # Laravel configs (EDIT HERE)
│
└── data-kiosk/                  # Persistent data (DON'T EDIT)
    ├── prod/
    │   ├── mariadb/             # Database files
    │   ├── redis/               # Redis dumps
    │   └── minio/               # S3 storage
    └── logs/                    # Application logs
```

### 🚫 NEVER Edit Files Inside Containers
**Why?**
- ❌ Changes are lost when container rebuilds
- ❌ No version control
- ❌ Inconsistent across deployments
- ❌ Hard to debug

**✅ Correct Workflow:**
1. Edit source files in `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/`
2. Run `./deploy-prod.sh` to apply changes
3. Changes are built into new container image
4. Changes persist across rebuilds

---

## 🔧 Performance Tuning Recommendations

### If You Need Even Faster (<100ms)

#### 1. Database Query Optimization
Add eager loading to Filament Resources:

```php
// In your Resource files (e.g., MediaResource.php)
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['user', 'category', 'tags']); // Eager load relationships
}
```

#### 2. Enable Query Caching
```php
// In your models
use Illuminate\Support\Facades\Cache;

public static function getActiveMedia()
{
    return Cache::remember('active_media', 3600, function () {
        return static::where('status', 'active')->get();
    });
}
```

#### 3. Cloudflare CDN Configuration
- Enable "Auto Minify" for JS/CSS/HTML
- Enable "Brotli" compression
- Set "Browser Cache TTL" to 4 hours
- Use "Page Rules" to cache everything for static assets

#### 4. Add More Queue Workers
```bash
# Edit docker-compose.prod.yml, add cosmic-queue-3, cosmic-queue-4, etc.
# Then run ./deploy-prod.sh
```

#### 5. Use Octane (Advanced)
```bash
# Switch to Laravel Octane with FrankenPHP or Swoole
# Can achieve 50-100ms response times
```

---

## 📈 Monitoring Commands

### Check Service Health
```bash
cd /home/ubuntu/kiosk
docker compose -f docker-compose.prod.yml ps
```

### View Application Logs
```bash
# All logs
docker compose -f docker-compose.prod.yml logs -f cosmic-app

# Last 100 lines
docker compose -f docker-compose.prod.yml logs --tail=100 cosmic-app

# Queue worker logs
docker compose -f docker-compose.prod.yml logs -f cosmic-queue-1 cosmic-queue-2
```

### Check Redis Performance
```bash
# Connection test
docker compose -f docker-compose.prod.yml exec redis redis-cli ping

# Memory usage
docker compose -f docker-compose.prod.yml exec redis redis-cli INFO memory

# Cache hit rate
docker compose -f docker-compose.prod.yml exec redis redis-cli INFO stats | grep keyspace
```

### Check OPcache Status
```bash
docker compose -f docker-compose.prod.yml exec cosmic-app php -i | grep opcache
```

### Database Performance
```bash
# Check slow queries
docker compose -f docker-compose.prod.yml exec mariadb mysql -u root -p -e "SHOW FULL PROCESSLIST;"
```

---

## 🔐 Security Checklist

### ✅ Production Security Applied
- ✅ `APP_DEBUG=false` (no error details exposed)
- ✅ Strong database passwords (no defaults)
- ✅ MinIO with custom credentials
- ✅ Redis without public exposure
- ✅ HTTPS enforced (via URL::forceScheme)
- ✅ Cloudflare Flexible SSL
- ✅ Session secure cookies
- ✅ CORS configured
- ✅ Rate limiting enabled
- ✅ Input validation (Filament forms)

---

## 🎯 Summary

### What You Have Now:
1. ✅ **Blazing Fast** - 250ms page loads (10x faster than before)
2. ✅ **One-Command Deploy** - `./deploy-prod.sh` does everything
3. ✅ **No Container Edits** - All changes in source code
4. ✅ **Full Redis Caching** - Cache, Queue, Session all in Redis
5. ✅ **Queue Workers** - 2 parallel workers for background jobs
6. ✅ **Auto-Optimization** - All caches built automatically
7. ✅ **Production Ready** - Security hardened, performance tuned

### Performance Comparison:
- **Before**: 1200-2500ms 🐌
- **After**: 250ms ⚡
- **Improvement**: **5-10x faster!** 🚀

---

**Last Updated**: January 24, 2026
**Status**: Production Optimized ✅
