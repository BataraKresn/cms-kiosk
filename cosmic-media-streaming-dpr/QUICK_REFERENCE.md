# Backend Fixes - Quick Reference Guide

## 🚀 Quick Start

### 1. Run Migration
```bash
cd /home/ubuntu/kiosk/cosmic-media-streaming-dpr
php artisan migrate
```

### 2. Test Heartbeat
```bash
# Send test heartbeat
curl -X POST http://localhost/api/devices/heartbeat \
  -H "Authorization: Bearer YOUR_DEVICE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"battery_level": 85}'
```

### 3. Monitor Status
```bash
# Run status monitor manually
php artisan devices:monitor-status --verbose

# Check logs
tail -f storage/logs/laravel.log | grep "Device status"
```

---

## 📋 Implementation Checklist

- [x] Database migration created and run
- [x] DeviceHeartbeatService implemented
- [x] HeartbeatRateLimiter middleware created
- [x] DeviceRegistrationController refactored
- [x] DeviceStatusMonitorCommand created
- [x] Scheduled task configured
- [x] Cache invalidation fixed
- [x] Structured logging added
- [x] Admin API for external service created
- [x] Python integration example provided

---

## 🎯 Key Features

### Server-Side Timeout Enforcement
```php
// Automatically runs every minute
php artisan devices:monitor-status
```

**Status Levels:**
- `Connected` - Heartbeat within 40 seconds
- `Temporarily Offline` - 40-300 seconds
- `Disconnected` - 300+ seconds

### Rate Limiting
- **Minimum interval**: 10 seconds between heartbeats
- **Maximum per minute**: 10 heartbeats
- **Response**: 429 Too Many Requests (device stays online)

### Atomic Updates
- Row-level locking on all status changes
- Prevents race conditions
- Transaction-based updates

### Cache Control
- Scoped invalidation per device
- No more global cache flushes
- Prevents cache thrashing

### Reconnection Signaling
```php
// Request device to reconnect
$service = app(\App\Services\DeviceHeartbeatService::class);
$service->requestReconnection($device, 300, 'Config update');

// Next heartbeat response:
// {"should_reconnect": true, "reconnect_delay_seconds": 300}
```

---

## 📊 Monitoring Commands

### Check Device Status
```bash
# View all devices
php artisan devices:monitor-status --verbose

# Dry run (no changes)
php artisan devices:monitor-status --dry-run --verbose
```

### Watch Logs
```bash
# Status transitions
tail -f storage/logs/laravel.log | grep "Device status transition"

# Heartbeats
tail -f storage/logs/laravel.log | grep "Heartbeat processed"

# Rate limiting
tail -f storage/logs/laravel.log | grep "rate limit"
```

### Database Queries
```sql
-- Recent status changes
SELECT id, name, status, previous_status, 
       last_status_change_at, status_change_reason
FROM remotes 
WHERE last_status_change_at > NOW() - INTERVAL 1 HOUR
ORDER BY last_status_change_at DESC;

-- Devices with flapping (3+ transitions in 5 min)
SELECT name, COUNT(*) as transitions
FROM remotes
WHERE last_status_change_at > NOW() - INTERVAL 5 MINUTE
GROUP BY id
HAVING transitions > 3;

-- Devices offline > 5 minutes
SELECT id, name, status, last_seen_at,
       TIMESTAMPDIFF(SECOND, last_seen_at, NOW()) as seconds_since_seen
FROM remotes
WHERE last_seen_at < NOW() - INTERVAL 5 MINUTE
  AND deleted_at IS NULL
ORDER BY last_seen_at DESC;
```

---

## 🔧 Configuration

### Per-Device Overrides
```php
$device = Remote::find(1);
$device->update([
    'heartbeat_interval_seconds' => 60,  // Custom: 60s interval
    'grace_period_seconds' => 120,       // Custom: 2 min grace
]);
```

### System Defaults
Located in `DeviceHeartbeatService`:
```php
DEFAULT_HEARTBEAT_INTERVAL = 30    // seconds
DEFAULT_GRACE_PERIOD = 60          // seconds  
OFFLINE_THRESHOLD = 300            // seconds (5 min)
```

### Rate Limits
Located in `HeartbeatRateLimiter`:
```php
MIN_HEARTBEAT_INTERVAL = 10        // seconds
MAX_HEARTBEATS_PER_MINUTE = 10     // count
```

---

## 🔌 External Service Integration

### Option 1: Use CMS API (Recommended)

**Setup:**
```bash
cd /home/ubuntu/kiosk/remote-android-device
export CMS_URL="http://localhost"
export CMS_API_TOKEN="your_admin_token_here"
python3 ping_service_coordinated.py
```

**API Endpoints:**
```bash
# Get devices needing ping
GET /api/admin/external-service/devices/needs-ping

# Submit ping result
POST /api/admin/external-service/device/{id}/ping
Body: {"ping_successful": true, "ping_status": "HTTP 200 OK"}

# Submit batch results
POST /api/admin/external-service/devices/ping-batch
Body: {"results": [...]}
```

### Option 2: Direct Database (Fallback)

If API not feasible, update SQL to:
```sql
UPDATE remotes 
SET 
  status = 'Disconnected',
  last_external_ping_at = NOW(),
  external_ping_status = 'Timeout',
  last_heartbeat_source = 'external_service'
WHERE id = ?
  AND (
    last_heartbeat_received_at IS NULL 
    OR last_heartbeat_received_at < NOW() - INTERVAL heartbeat_interval_seconds SECOND
  );
```

---

## 🐛 Troubleshooting

### Issue: Status Flapping

**Check:**
```sql
SELECT name, status, previous_status, status_change_reason, last_heartbeat_source
FROM remotes
WHERE last_status_change_at > NOW() - INTERVAL 10 MINUTE
ORDER BY last_status_change_at DESC;
```

**Look for:**
- Rapid transitions (< 30s apart)
- Source alternating between `device` and `external_service`
- Reason containing "grace period" repeatedly

**Fix:**
- Increase `grace_period_seconds` for affected devices
- Check network stability
- Verify external service respects CMS API

### Issue: Rate Limiting Too Aggressive

**Symptoms:**
- Devices getting 429 responses
- Logs show "rate limit exceeded"

**Fix:**
```php
// In HeartbeatRateLimiter.php, increase:
const MIN_HEARTBEAT_INTERVAL = 5;  // Lower from 10 to 5
const MAX_HEARTBEATS_PER_MINUTE = 20;  // Increase from 10 to 20
```

### Issue: Devices Stuck Offline

**Check:**
```sql
SELECT id, name, status, last_seen_at, last_heartbeat_received_at
FROM remotes
WHERE status = 'Disconnected'
  AND last_heartbeat_received_at > NOW() - INTERVAL 1 MINUTE;
```

**If devices recently heartbeated but marked offline:**
```bash
# Force status check
php artisan devices:monitor-status

# Check for errors in logs
tail -100 storage/logs/laravel.log | grep -i error
```

### Issue: High Database Load

**Check:**
```sql
SHOW PROCESSLIST;
-- Look for many UPDATE queries on remotes table
```

**Verify:**
- Scheduled command not running multiple times simultaneously
- Rate limiter is active (check middleware)
- No direct DB writes bypassing service

---

## 📈 Performance Metrics

### Before Fixes
- Cache flush: ~3.3/sec (100 devices)
- Status flapping: Common under jitter
- Race conditions: Frequent
- Database locks: None

### After Fixes
- Cache invalidation: Scoped per device
- Status flapping: Prevented by grace periods
- Race conditions: Eliminated by locking
- Database locks: Row-level only

### Monitor
```bash
# Cache hit ratio (should increase)
php artisan tinker
>>> Cache::getStore()->getRedis()->info('stats')

# Heartbeat response time (should be < 50ms)
# Check Laravel logs for "Heartbeat processed" entries

# Database connections (should decrease)
mysql> SHOW STATUS LIKE 'Threads_connected';
```

---

## 🔄 Rollback

If issues occur:

1. **Disable rate limiting:**
   ```php
   // routes/api.php - remove middleware
   Route::post('/devices/heartbeat', [...]); // Remove ->middleware()
   ```

2. **Disable scheduled monitor:**
   ```php
   // app/Console/Kernel.php - comment out
   // $schedule->command('devices:monitor-status')->...
   ```

3. **Rollback migration:**
   ```bash
   php artisan migrate:rollback --step=1
   ```

4. **Revert controller:**
   ```bash
   git checkout HEAD -- app/Http/Controllers/Api/DeviceRegistrationController.php
   ```

---

## 📝 Testing Scenarios

### Test 1: Normal Heartbeat
```bash
# Send heartbeat every 30 seconds
for i in {1..5}; do
  curl -X POST http://localhost/api/devices/heartbeat \
    -H "Authorization: Bearer TOKEN" \
    -d '{"battery_level": 85}'
  sleep 30
done

# Verify: Device stays Connected
```

### Test 2: Rate Limiting
```bash
# Send 5 heartbeats rapidly
for i in {1..5}; do
  curl -X POST http://localhost/api/devices/heartbeat \
    -H "Authorization: Bearer TOKEN" \
    -d '{}'
  sleep 1
done

# Verify: First succeeds, others return 429
```

### Test 3: Timeout → Recovery
```bash
# Send heartbeat
curl -X POST http://localhost/api/devices/heartbeat -H "Authorization: Bearer TOKEN"

# Wait 45 seconds (exceeds 30s interval but within 60s grace)
sleep 45
php artisan devices:monitor-status --verbose
# Verify: Status = "Temporarily Offline"

# Send heartbeat again
curl -X POST http://localhost/api/devices/heartbeat -H "Authorization: Bearer TOKEN"
# Verify: Status = "Connected"
```

### Test 4: Reconnection Signal
```php
// In tinker
$device = Remote::first();
$service = app(\App\Services\DeviceHeartbeatService::class);
$service->requestReconnection($device, 60, 'Test reconnection');

// Send heartbeat
// curl...

// Check response has should_reconnect: true
```

---

## 📞 Support

**Documentation:**
- Full implementation: `IMPLEMENTATION_BACKEND_FIXES.md`
- Analysis: `CMS_BACKEND_SYSTEM_ANALYSIS.md`

**Logs:**
- Application: `storage/logs/laravel.log`
- Web server: Check nginx/apache logs
- Database: MySQL slow query log

**Commands:**
```bash
php artisan devices:monitor-status --help
php artisan tinker  # Interactive shell
php artisan route:list | grep heartbeat
```
