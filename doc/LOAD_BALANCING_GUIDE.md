# Load Balancing & Queue Architecture Guide
**Cosmic Media Streaming Platform - High Performance Scaling**

---

## 🏗️ Architecture Overview

### Current Infrastructure (After Scaling)

```
                          ┌─────────────────┐
                          │   Cloudflare    │
                          │   (CDN + SSL)   │
                          └────────┬────────┘
                                   │
                          ┌────────▼────────┐
                          │  NPM (Reverse   │
                          │     Proxy)      │
                          └────────┬────────┘
                                   │
                          ┌────────▼────────┐
                          │   Nginx Load    │
                          │    Balancer     │
                          │  (port 8080)    │
                          └────────┬────────┘
                                   │
               ┌───────────────────┼───────────────────┐
               │                   │                   │
        ┌──────▼──────┐    ┌──────▼──────┐    ┌──────▼──────┐
        │ cosmic-app-1│    │ cosmic-app-2│    │ cosmic-app-3│
        │   (Laravel) │    │   (Laravel) │    │   (Laravel) │
        └──────┬──────┘    └──────┬──────┘    └──────┬──────┘
               │                   │                   │
               └───────────────────┼───────────────────┘
                                   │
                          ┌────────▼────────┐
                          │   Redis Queue   │
                          │   (Database 0)  │
                          └────────┬────────┘
                                   │
        ┌──────────────────────────┼──────────────────────────┐
        │                          │                          │
    ┌───▼────┐               ┌─────▼─────┐            ┌──────▼──────┐
    │ VIDEO  │               │   IMAGE   │            │   DEFAULT   │
    │ QUEUE  │               │   QUEUE   │            │    QUEUE    │
    └───┬────┘               └─────┬─────┘            └──────┬──────┘
        │                          │                          │
    3 Workers                  3 Workers                  2 Workers
    ├─ video-1                 ├─ image-1                 ├─ default-1
    ├─ video-2                 ├─ image-2                 └─ default-2
    └─ video-3                 └─ image-3
```

---

## 🔄 Load Balancing Strategy

### Nginx Load Balancer Configuration

**Algorithm**: `least_conn` (Least Connections)
- Routes requests to server with fewest active connections
- Better for long-running requests (uploads, processing)
- More efficient than round-robin for mixed workloads

**Upstream Configuration**:
```nginx
upstream cosmic_app_backend {
    least_conn;
    server cosmic-app-1-prod:80 max_fails=3 fail_timeout=30s weight=1;
    server cosmic-app-2-prod:80 max_fails=3 fail_timeout=30s weight=1;
    server cosmic-app-3-prod:80 max_fails=3 fail_timeout=30s weight=1;
    keepalive 64;
}
```

**Features**:
- ✅ **Health Checks**: Auto-removes failed servers (max_fails=3)
- ✅ **Fail Timeout**: Retries failed server after 30s
- ✅ **Keepalive**: Reuses connections (64 pooled)
- ✅ **Equal Weight**: All servers get equal traffic (weight=1)

### Request Distribution

**Example Traffic Flow**:
1. User A → cosmic-app-1 (0 connections)
2. User B → cosmic-app-2 (0 connections)
3. User C → cosmic-app-3 (0 connections)
4. User D → cosmic-app-1 (User A finished, now 0 again)
5. Large Upload → cosmic-app-2 (holds connection 2 minutes)
6. Next users → cosmic-app-1 & cosmic-app-3 (cosmic-app-2 busy)

**Benefits**:
- 🚀 **3x Throughput**: Handle 3x more concurrent requests
- ⚡ **Faster Response**: Distribute load evenly
- 🛡️ **High Availability**: If one fails, others continue
- 📈 **Scalable**: Can easily add cosmic-app-4, cosmic-app-5...

---

## 🎬 Queue Architecture

### Queue Types (Named Queues)

#### 1. **VIDEO Queue** (3 Workers)
**Purpose**: Heavy processing tasks (video encoding, FFmpeg)

**Workers**:
- `cosmic-queue-video-1-prod`
- `cosmic-queue-video-2-prod`
- `cosmic-queue-video-3-prod`

**Configuration**:
```bash
php artisan queue:work \
    --queue=video \
    --sleep=3 \
    --tries=3 \
    --max-time=7200 \    # 2 hours (long running)
    --timeout=1800       # 30 min per job
```

**Use Cases**:
- Video transcoding (MP4, WebM, etc.)
- HLS stream generation
- Video thumbnail extraction
- FFmpeg operations

#### 2. **IMAGE Queue** (3 Workers)
**Purpose**: Image optimization and manipulation

**Workers**:
- `cosmic-queue-image-1-prod`
- `cosmic-queue-image-2-prod`
- `cosmic-queue-image-3-prod`

**Configuration**:
```bash
php artisan queue:work \
    --queue=image \
    --sleep=2 \
    --tries=3 \
    --max-time=3600 \    # 1 hour
    --timeout=600        # 10 min per job
```

**Use Cases**:
- Image resizing
- Thumbnail generation
- Watermarking
- Format conversion (JPEG, PNG, WebP)

#### 3. **DEFAULT Queue** (2 Workers)
**Purpose**: General background tasks

**Workers**:
- `cosmic-queue-default-1-prod`
- `cosmic-queue-default-2-prod`

**Configuration**:
```bash
php artisan queue:work \
    --queue=default \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \    # 1 hour
    --timeout=600        # 10 min per job
```

**Use Cases**:
- Email sending
- PDF generation
- Notifications
- Database cleanup
- Cache warming

---

## 💻 How to Use in Your Code

### Dispatching Video Jobs

**Example: In your controller or Filament Resource**

```php
<?php

namespace App\Filament\Resources\MediaVideoResource\Pages;

use App\Jobs\ProcessVideoUpload;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaVideo extends CreateRecord
{
    protected function afterCreate(): void
    {
        // Dispatch to VIDEO queue
        ProcessVideoUpload::dispatch($this->record->file_path)
            ->onQueue('video');  // ← Routes to video workers
    }
}
```

### Dispatching Image Jobs

```php
<?php

namespace App\Filament\Resources\MediaImageResource\Pages;

use App\Jobs\ProcessImageUpload;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaImage extends CreateRecord
{
    protected function afterCreate(): void
    {
        // Dispatch to IMAGE queue
        ProcessImageUpload::dispatch($this->record->file_path)
            ->onQueue('image');  // ← Routes to image workers
    }
}
```

### Dispatching to Default Queue

```php
use App\Jobs\SendNotificationEmail;

// No onQueue() = goes to 'default' queue
SendNotificationEmail::dispatch($user, $message);

// Or explicitly:
SendNotificationEmail::dispatch($user, $message)
    ->onQueue('default');
```

---

## 📊 Performance Comparison

### Before Scaling
```
1 App Instance      = Max 50 concurrent requests
2 Queue Workers     = Process 2 jobs at once
```

**Bottlenecks**:
- ❌ Single app = limited throughput
- ❌ Mixed queues = video jobs block image jobs
- ❌ No redundancy = single point of failure

### After Scaling
```
3 App Instances     = Max 150 concurrent requests (3x)
8 Queue Workers     = Process 8 jobs simultaneously (4x)
  ├─ 3 video workers
  ├─ 3 image workers
  └─ 2 default workers
```

**Improvements**:
- ✅ **3x Web Throughput**: More concurrent users
- ✅ **4x Queue Throughput**: Faster job processing
- ✅ **Queue Isolation**: Video/image jobs don't block each other
- ✅ **High Availability**: Services survive individual failures
- ✅ **Better Resource Utilization**: Dedicated workers per job type

**Capacity Example**:
- 3 video uploads → All processed simultaneously (1 per worker)
- 3 image uploads → All processed simultaneously (1 per worker)
- 2 PDF generations → Both processed simultaneously
- **Total**: 8 jobs running in parallel!

---

## 🔍 Monitoring & Management

### Check Load Balancer Status

```bash
# Check which app instances are running
docker compose -f docker-compose.prod.yml ps | grep cosmic-app

# Expected output:
# cosmic-app-1-prod    running   80/tcp
# cosmic-app-2-prod    running   80/tcp
# cosmic-app-3-prod    running   80/tcp
```

### Monitor Queue Workers

```bash
# Check video workers
docker compose -f docker-compose.prod.yml logs -f cosmic-queue-video-1 cosmic-queue-video-2 cosmic-queue-video-3

# Check image workers
docker compose -f docker-compose.prod.yml logs -f cosmic-queue-image-1 cosmic-queue-image-2 cosmic-queue-image-3

# Check default workers
docker compose -f docker-compose.prod.yml logs -f cosmic-queue-default-1 cosmic-queue-default-2
```

### Check Queue Status

```bash
# Inside any app container
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan queue:work --once

# View jobs per queue
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan queue:monitor video image default
```

### View Failed Jobs

```bash
# List failed jobs
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan queue:failed

# Retry all failed jobs
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan queue:retry all

# Retry specific job
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan queue:retry [job-id]
```

### Check Nginx Load Balancer Stats

```bash
# Inside nginx container
docker compose -f docker-compose.prod.yml exec nginx cat /var/log/nginx/access.log | tail -100

# Count requests per upstream
docker compose -f docker-compose.prod.yml exec nginx grep "cosmic-app" /var/log/nginx/access.log | awk '{print $10}' | sort | uniq -c
```

---

## ⚙️ Scaling Further (If Needed)

### Add More App Instances

**Step 1**: Edit `docker-compose.prod.yml`

```yaml
cosmic-app-4:
  image: kiosk-cosmic-app:prod
  container_name: cosmic-app-4-prod
  # ... copy from cosmic-app-1 config
```

**Step 2**: Edit `nginx.conf`

```nginx
upstream cosmic_app_backend {
    least_conn;
    server cosmic-app-1-prod:80 weight=1;
    server cosmic-app-2-prod:80 weight=1;
    server cosmic-app-3-prod:80 weight=1;
    server cosmic-app-4-prod:80 weight=1;  # ← Add this
    keepalive 64;
}
```

**Step 3**: Deploy

```bash
./deploy-prod.sh
```

### Add More Queue Workers

Same process - just add more service definitions:

```yaml
cosmic-queue-video-4:
  # ... copy from cosmic-queue-video-1
```

**No nginx changes needed** - workers connect directly to Redis.

---

## 🎯 Best Practices

### 1. **Queue Naming Convention**
```php
// Always use explicit queue names
$job->onQueue('video');    // ✅ Good
$job->onQueue('image');    // ✅ Good
dispatch($job);            // ⚠️ Goes to 'default' (acceptable for misc jobs)
```

### 2. **Job Timeouts**
```php
class ProcessVideoUpload implements ShouldQueue
{
    public $timeout = 1800;  // Match or less than worker --timeout
    public $tries = 3;       // Match worker --tries
}
```

### 3. **Monitor Job Failures**
```php
public function failed(\Throwable $exception)
{
    // Send alert to admin
    \Log::error("Job failed: {$exception->getMessage()}");
    
    // Optionally notify via email/Slack
    \Notification::route('mail', 'admin@example.com')
        ->notify(new JobFailedNotification($exception));
}
```

### 4. **Health Checks**
```bash
# Add to crontab for monitoring
*/5 * * * * docker compose -f /home/ubuntu/kiosk/docker-compose.prod.yml ps | grep -q "cosmic-app-1-prod.*Up" || echo "App 1 is down!" | mail -s "Alert" admin@example.com
```

---

## 📋 Summary

### What You Have Now:

| Component | Count | Purpose |
|-----------|-------|---------|
| **App Instances** | 3 | Handle web requests (load balanced) |
| **Video Workers** | 3 | Process video encoding jobs |
| **Image Workers** | 3 | Process image optimization jobs |
| **Default Workers** | 2 | Process misc background jobs |
| **Total Containers** | **11** | (3 app + 8 workers) |

### Performance Gains:

- 🚀 **3x Web Capacity**: 50 → 150 concurrent users
- ⚡ **4x Queue Capacity**: 2 → 8 parallel jobs
- 🎯 **Queue Isolation**: Video/Image jobs don't interfere
- 🛡️ **High Availability**: Survives individual failures
- 📈 **Easy Scaling**: Add more instances anytime

### Deployment:

```bash
cd /home/ubuntu/kiosk
./deploy-prod.sh
```

**Everything is automatic!** No manual configuration needed.

---

**Last Updated**: January 24, 2026  
**Architecture**: Load Balanced + Queue Separated  
**Status**: Production Ready 🚀
