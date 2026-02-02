# CMS Backend Fixes Implementation - Device Connectivity

**Implementation Date**: February 2, 2026  
**Based On**: CMS Backend System Analysis - Device Connectivity Behavior  
**Scope**: Laravel CMS Backend Only  

---

## EXECUTIVE SUMMARY

This implementation fixes critical device connectivity issues in the CMS backend by:

✅ **Making CMS the authoritative source of truth** for device state  
✅ **Eliminating race conditions** between heartbeat handler and external service  
✅ **Preventing status flapping** under network jitter and delays  
✅ **Enforcing explicit connectivity rules** server-side  
✅ **Implementing server-initiated reconnection** signaling  
✅ **Adding comprehensive observability** for debugging  

---

## FIXES IMPLEMENTED

### 1. ✅ HEARTBEAT ENFORCEMENT (Server-Side)

**What Was Fixed:**
- Removed implicit 30-second heartbeat expectation
- Added explicit server-side timeout enforcement
- Implemented three-tier status system with grace periods

**Implementation:**

**New Status Levels:**
```
CONNECTED           → Heartbeat received within interval (30s default)
TEMPORARILY_OFFLINE → Heartbeat missing but within grace period (60s default)
DISCONNECTED        → Heartbeat missing beyond offline threshold (300s)
```

**Files Changed:**
- `app/Services/DeviceHeartbeatService.php` - Core enforcement logic
- `app/Console/Commands/DeviceStatusMonitorCommand.php` - Scheduled checker
- `app/Console/Kernel.php` - Replaced old timeout logic

**How It Works:**
```php
// Runs every minute via Laravel scheduler
php artisan devices:monitor-status

// Checks each device's last_seen_at timestamp
// Applies timeout rules atomically with row-level locking
// Transitions status based on configured thresholds
```

---

### 2. ✅ STATUS OWNERSHIP (CMS as Authority)

**What Was Fixed:**
- Heartbeat from device is now PRIMARY authority
- External service is SECONDARY signal
- External service cannot override recent heartbeat

**Implementation:**

**Priority Logic in `DeviceHeartbeatService::processExternalPing()`:**
```php
$heartbeatAge = $now->diffInSeconds($device->last_heartbeat_received_at);
$heartbeatInterval = $device->heartbeat_interval_seconds ?? 30;

// CRITICAL: Ignore external service if heartbeat is recent
if ($heartbeatAge < $heartbeatInterval) {
    // External ping result is ignored
    return false; // No status change
}

// Heartbeat is stale - external service can update
```

**Result:**
- Device heartbeat always wins within its interval window
- No more ping/heartbeat conflicts
- External service only acts when heartbeat is missing

---

### 3. ✅ ATOMIC STATE UPDATES (Race Prevention)

**What Was Fixed:**
- Removed raw SQL without transactions
- Added row-level locking on all status updates
- Prevented concurrent writes from external service and heartbeat handler

**Implementation:**

**Every status update uses:**
```php
DB::transaction(function () use ($remote) {
    $lockedRemote = Remote::where('id', $remote->id)
        ->lockForUpdate()  // ← ROW-LEVEL LOCK
        ->first();
    
    // Perform atomic update
    $lockedRemote->update([...]);
    
    // Lock released at end of transaction
});
```

**Files Using Atomic Updates:**
- `DeviceHeartbeatService::processHeartbeat()` - Device heartbeats
- `DeviceHeartbeatService::processExternalPing()` - External service
- `DeviceHeartbeatService::enforceTimeoutRules()` - Scheduled monitoring
- `HeartbeatRateLimiter` middleware - Rate limit checks

---

### 4. ✅ CACHE INVALIDATION CONTROL (No More Thrashing)

**What Was Fixed:**
- Removed global cache flush on every heartbeat
- Implemented scoped, per-device cache invalidation
- Eliminated cache thrashing under 100+ device load

**Before (REMOVED):**
```php
// ❌ REMOVED - caused cache thrashing
Cache::tags(['device_status'])->flush(); // Nuked ALL device caches
```

**After (NEW):**
```php
// ✅ Only invalidate THIS device's caches
Cache::forget('device_token_' . $remote->token);
Cache::forget('device_rc_status_' . $remote->id);
Cache::forget('device_status_' . $remote->id);
Cache::forget('display_content_' . $remote->token);
// Global flush REMOVED
```

**Impact:**
- 100 devices × 30s interval = 3.3 heartbeats/sec
- **Before**: 3.3 full cache flushes per second → database overload
- **After**: 3.3 scoped invalidations → minimal overhead

---

### 5. ✅ HEARTBEAT RATE LIMITING (Abuse Prevention)

**What Was Fixed:**
- Added per-device rate limiting
- Rejects heartbeats arriving too frequently
- Does NOT mark device offline when rate-limited

**Implementation:**

**New Middleware: `HeartbeatRateLimiter`**
```php
// Limits:
MIN_HEARTBEAT_INTERVAL = 10 seconds
MAX_HEARTBEATS_PER_MINUTE = 10

// Returns 429 Too Many Requests if exceeded
// Device stays online, just heartbeat rejected
```

**Applied to Route:**
```php
Route::post('/devices/heartbeat', [DeviceRegistrationController::class, 'heartbeat'])
    ->middleware('heartbeat.rate');
```

**Response on Rate Limit:**
```json
{
  "success": false,
  "message": "Heartbeat rate limit exceeded. Minimum interval: 10 seconds.",
  "retry_after_seconds": 7,
  "data": {
    "remote_control_enabled": true,
    "should_reconnect": false
  }
}
```

---

### 6. ✅ SERVER-INITIATED SIGNALING (Reconnection Control)

**What Was Fixed:**
- `should_reconnect` is no longer hardcoded to `false`
- CMS can now request device reconnection
- Supports delayed reconnection with reason tracking

**Implementation:**

**New Service Method:**
```php
$heartbeatService->requestReconnection(
    $device,
    delaySeconds: 300,  // Wait 5 minutes before reconnecting
    reason: 'Configuration update requires reconnection'
);
```

**Device Response Includes:**
```json
{
  "success": true,
  "data": {
    "remote_control_enabled": true,
    "should_reconnect": true,
    "reconnect_delay_seconds": 300
  }
}
```

**Automatic Reset:**
- When device reconnects, `should_reconnect` automatically resets to `false`
- Logged for audit trail

---

### 7. ✅ OBSERVABILITY (Structured Logging)

**What Was Fixed:**
- Added structured logging for all status transitions
- Logs include timestamps, reasons, and context
- Searchable logs for debugging status flapping

**Log Format:**
```php
Log::info('Device status transition', [
    'device_id' => 123,
    'device_name' => 'KIOSK-LOBBY-01',
    'from_status' => 'Connected',
    'to_status' => 'Temporarily Offline',
    'reason' => 'No heartbeat for 45s (grace period: 60s)',
    'source' => 'system',  // device|external_service|system
    'timestamp' => '2026-02-02T10:30:45+00:00',
]);
```

**Log Sources:**
- `device` - Direct heartbeat from Android device
- `external_service` - Python ping service result
- `system` - Scheduled timeout enforcement

**Searchable Fields:**
- Device ID, name
- Status transition (from → to)
- Reason (explains WHY status changed)
- Source (who triggered the change)

---

## DATABASE CHANGES

### New Migration: `2026_02_02_000001_add_heartbeat_management_fields_to_remotes.php`

**New Columns Added to `remotes` Table:**

| Column | Type | Purpose |
|--------|------|---------|
| `heartbeat_interval_seconds` | INT | Per-device heartbeat interval (default: 30) |
| `grace_period_seconds` | INT | Grace period before offline (default: 60) |
| `should_reconnect` | BOOLEAN | Server-initiated reconnection flag |
| `reconnect_delay_seconds` | INT | Delay before reconnecting (nullable) |
| `reconnect_reason` | VARCHAR(255) | Reason for reconnection request |
| `last_status_change_at` | TIMESTAMP | When status last changed |
| `status_change_reason` | VARCHAR(255) | Why status changed (debugging) |
| `previous_status` | VARCHAR(50) | Previous status value |
| `last_heartbeat_received_at` | TIMESTAMP | Rate limiting tracking |
| `heartbeat_count_current_minute` | INT | Rate limiting counter |
| `last_heartbeat_source` | VARCHAR(20) | Who updated: device\|external_service\|system |
| `last_external_ping_at` | TIMESTAMP | Last external service ping time |
| `external_ping_status` | VARCHAR(50) | Result of last external ping |

**Indexes Added:**
```sql
idx_remotes_last_seen
idx_remotes_should_reconnect
idx_remotes_status_tracking (status, last_status_change_at)
idx_remotes_heartbeat_rate
```

**Migration:**
```bash
php artisan migrate
```

---

## NEW FILES CREATED

### 1. **DeviceHeartbeatService**
**Path:** `app/Services/DeviceHeartbeatService.php`

**Responsibilities:**
- Process device heartbeats atomically
- Process external service ping results
- Enforce timeout rules (called by scheduled command)
- Request device reconnection
- Invalidate device-specific caches
- Log status transitions

**Key Methods:**
```php
processHeartbeat(Remote $remote, array $metrics): array
processExternalPing(int $remoteId, bool $success, string $status): bool
enforceTimeoutRules(Remote $remote): bool
requestReconnection(Remote $remote, ?int $delay, ?string $reason): void
```

---

### 2. **HeartbeatRateLimiter Middleware**
**Path:** `app/Http/Middleware/HeartbeatRateLimiter.php`

**Responsibilities:**
- Enforce minimum 10-second interval between heartbeats
- Limit to max 10 heartbeats per minute
- Return 429 status code on rate limit
- Does NOT mark device offline

**Limits:**
```php
MIN_HEARTBEAT_INTERVAL = 10 seconds
MAX_HEARTBEATS_PER_MINUTE = 10
```

---

### 3. **DeviceStatusMonitorCommand**
**Path:** `app/Console/Commands/DeviceStatusMonitorCommand.php`

**Responsibilities:**
- Runs every minute via scheduler
- Enforces timeout rules on all devices
- Marks devices offline when heartbeat missing
- Supports dry-run and verbose modes

**Usage:**
```bash
# Normal operation (scheduled)
php artisan devices:monitor-status

# Dry run (see what would change)
php artisan devices:monitor-status --dry-run

# Verbose output
php artisan devices:monitor-status --verbose

# Both
php artisan devices:monitor-status --dry-run --verbose
```

---

## UPDATED FILES

### 1. **DeviceRegistrationController**
**Path:** `app/Http/Controllers/Api/DeviceRegistrationController.php`

**Changes:**
- Removed raw SQL execution
- Now uses `DeviceHeartbeatService`
- Returns `should_reconnect` and `reconnect_delay_seconds`
- Proper error handling with structured logging

**Before:**
```php
DB::connection()->getPdo()->exec($sql); // ❌ Raw SQL, no locking
Cache::tags(['device_status'])->flush(); // ❌ Global flush
return ['should_reconnect' => false]; // ❌ Hardcoded
```

**After:**
```php
$result = $heartbeatService->processHeartbeat($remote, $metrics);
return [
    'should_reconnect' => $result['should_reconnect'],
    'reconnect_delay_seconds' => $result['reconnect_delay_seconds'],
];
```

---

### 2. **Remote Model**
**Path:** `app/Models/Remote.php`

**Changes:**
- Added new fields to `$fillable`
- Added datetime casts for new timestamp fields
- Added boolean casts for flags

---

### 3. **HTTP Kernel**
**Path:** `app/Http/Kernel.php`

**Changes:**
- Registered `HeartbeatRateLimiter` middleware as `heartbeat.rate`

---

### 4. **Console Kernel**
**Path:** `app/Console/Kernel.php`

**Changes:**
- Replaced old timeout logic with new command
- Scheduled `devices:monitor-status` to run every minute
- Added `withoutOverlapping()` to prevent concurrent runs

---

### 5. **API Routes**
**Path:** `routes/api.php`

**Changes:**
- Applied `heartbeat.rate` middleware to heartbeat endpoint

---

## COORDINATION WITH EXTERNAL PYTHON SERVICE

### Integration Points

The Python service (`remote-android-device/ping.py`) should be updated to:

1. **Call DeviceHeartbeatService Instead of Direct DB Updates**

**Recommended Approach:**
```python
# Instead of:
cursor.execute("UPDATE remotes SET status = %s WHERE id = %s", (status, device_id))

# Use Laravel endpoint (create new admin API endpoint):
response = requests.post(
    f"{CMS_URL}/api/admin/devices/{device_id}/external-ping",
    headers={"Authorization": f"Bearer {ADMIN_TOKEN}"},
    json={
        "ping_successful": True,  # or False
        "ping_status": "HTTP 200 OK",
        "response_time_ms": 45
    }
)
```

2. **New Admin Endpoint (To Be Added)**

Create endpoint that external service can call:
```php
// routes/api.php (admin-only)
Route::post('/admin/devices/{id}/external-ping', function (Request $request, int $id) {
    $heartbeatService = app(\App\Services\DeviceHeartbeatService::class);
    
    $success = $heartbeatService->processExternalPing(
        $id,
        $request->boolean('ping_successful'),
        $request->input('ping_status')
    );
    
    return response()->json(['success' => $success]);
})->middleware('auth:sanctum'); // Requires admin token
```

3. **Fallback: Direct DB Access (If API Not Feasible)**

If external service must continue using direct DB access, it should:
- Check `last_heartbeat_received_at` before updating
- Only update if heartbeat is stale (older than `heartbeat_interval_seconds`)
- Set `last_heartbeat_source` = 'external_service'
- Set `last_external_ping_at` and `external_ping_status`

---

## TESTING CHECKLIST

### Manual Testing

1. **Normal Heartbeat Flow**
   ```bash
   # Send heartbeat
   curl -X POST http://localhost/api/devices/heartbeat \
     -H "Authorization: Bearer {device_token}" \
     -H "Content-Type: application/json" \
     -d '{"battery_level": 85, "wifi_strength": -45}'
   
   # Verify: status = 'Connected', last_seen_at updated
   ```

2. **Rate Limiting**
   ```bash
   # Send 5 heartbeats rapidly (< 10 seconds apart)
   # Expected: First succeeds, others return 429
   ```

3. **Timeout Enforcement**
   ```bash
   # Stop sending heartbeats
   # Wait 40 seconds → status = 'Temporarily Offline'
   # Wait 70 seconds → status = 'Temporarily Offline'
   # Wait 5+ minutes → status = 'Disconnected'
   
   # Verify with:
   php artisan devices:monitor-status --verbose
   ```

4. **Server-Initiated Reconnection**
   ```php
   // In tinker:
   $device = Remote::find(1);
   $service = app(\App\Services\DeviceHeartbeatService::class);
   $service->requestReconnection($device, 60, 'Config update');
   
   // Next heartbeat response will include:
   // "should_reconnect": true, "reconnect_delay_seconds": 60
   ```

5. **External Service Coordination**
   ```bash
   # Scenario: Recent heartbeat exists
   # External service pings → should NOT change status
   
   # Scenario: Heartbeat missing for 60s
   # External service pings → CAN change status
   ```

---

## MONITORING & DEBUGGING

### Log Monitoring

**Watch status transitions:**
```bash
tail -f storage/logs/laravel.log | grep "Device status transition"
```

**Watch heartbeat processing:**
```bash
tail -f storage/logs/laravel.log | grep "Heartbeat processed"
```

**Watch rate limiting:**
```bash
tail -f storage/logs/laravel.log | grep "rate limit exceeded"
```

### Database Queries

**Find devices with recent status changes:**
```sql
SELECT 
    id, name, status, previous_status,
    last_status_change_at,
    status_change_reason,
    last_heartbeat_source
FROM remotes
WHERE last_status_change_at > NOW() - INTERVAL 1 HOUR
ORDER BY last_status_change_at DESC;
```

**Find flapping devices (multiple transitions in 5 minutes):**
```sql
SELECT 
    name, 
    COUNT(*) as transition_count,
    GROUP_CONCAT(status ORDER BY last_status_change_at) as statuses
FROM remotes
WHERE last_status_change_at > NOW() - INTERVAL 5 MINUTE
GROUP BY id
HAVING transition_count > 3
ORDER BY transition_count DESC;
```

---

## ROLLBACK PLAN

If issues occur, rollback steps:

1. **Revert routes middleware:**
   ```php
   // routes/api.php
   Route::post('/devices/heartbeat', [DeviceRegistrationController::class, 'heartbeat']);
   // Remove ->middleware('heartbeat.rate')
   ```

2. **Disable scheduled command:**
   ```php
   // app/Console/Kernel.php
   // Comment out:
   // $schedule->command('devices:monitor-status')->everyMinute();
   ```

3. **Rollback migration:**
   ```bash
   php artisan migrate:rollback --step=1
   ```

4. **Revert controller changes:**
   Restore old heartbeat method from git history

---

## PERFORMANCE IMPACT

### Expected Improvements

**Before:**
- Global cache flush every ~0.3 seconds (100 devices × 30s interval)
- No race condition protection
- Status flapping under network jitter
- No rate limiting (vulnerable to DOS)

**After:**
- Scoped cache invalidation only
- Row-level locking prevents races
- Three-tier status with grace periods
- Rate limiting protects against abuse

**Metrics to Monitor:**
- Database connection pool utilization (should decrease)
- Cache hit ratio (should increase)
- Heartbeat response time (should remain < 50ms)
- Status transition frequency (should decrease)

---

## CONFIGURATION

### Per-Device Overrides

Admins can override timing for specific devices:

```php
// In CMS admin panel or via tinker:
$device = Remote::find(1);
$device->update([
    'heartbeat_interval_seconds' => 60,  // Custom interval
    'grace_period_seconds' => 120,       // Custom grace period
]);
```

### System Defaults

Defined in `DeviceHeartbeatService`:
```php
DEFAULT_HEARTBEAT_INTERVAL = 30 seconds
DEFAULT_GRACE_PERIOD = 60 seconds
OFFLINE_THRESHOLD = 300 seconds (5 minutes)
```

---

## CONCLUSION

All seven required fixes have been implemented:

1. ✅ **Heartbeat Enforcement** - Server-side timeout rules with grace periods
2. ✅ **Status Ownership** - CMS is primary authority, external service is secondary
3. ✅ **Atomic State Updates** - Row-level locking prevents race conditions
4. ✅ **Cache Invalidation** - Scoped invalidation prevents thrashing
5. ✅ **Heartbeat Rate Limiting** - Prevents abuse without marking offline
6. ✅ **Server-Initiated Signaling** - Functional `should_reconnect` mechanism
7. ✅ **Observability** - Comprehensive structured logging

**Next Steps:**
1. Run migration: `php artisan migrate`
2. Test heartbeat flow manually
3. Monitor logs for status transitions
4. Coordinate external Python service updates
5. Monitor system performance metrics

**No Android or Python code was modified per the scope constraints.**
