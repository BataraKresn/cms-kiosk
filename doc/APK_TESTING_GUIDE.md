# APK Testing Guide - Device Connection Fixes

**Date:** February 2, 2026  
**Issue:** Device shows as "Disconnected" in CMS UI despite registration  
**Status:** Backend fixes completed, APK rebuild required

---

## 🔧 Fixes Implemented

### 1. **Backend Fixes (COMPLETED ✅)**

#### DeviceHeartbeatService.php
- **Fixed:** Hardcoded `should_reconnect: false` causing APK to stop heartbeat
- **Location:** Line 142
- **Change:** Returns actual database value instead of hardcoded false
- **Impact:** APK will now continue sending heartbeats automatically

#### DeviceRegistrationController.php
- **Fixed:** New devices not getting default heartbeat settings
- **Added:** Default values on registration:
  - `should_reconnect: true`
  - `heartbeat_interval_seconds: 30`
  - `grace_period_seconds: 300`
- **Impact:** All newly registered devices will have heartbeat enabled by default

#### DeviceHeartbeatService.php (Additional)
- **Fixed:** Buggy logic that auto-reset `should_reconnect` to false after each heartbeat
- **Removed:** Lines 121-131 problematic reconnect flag reset
- **Added:** Auto-set `should_reconnect: true` to keep heartbeat alive
- **Added:** `$lockedRemote->refresh()` to get latest database values
- **Impact:** Heartbeat will persist indefinitely unless manually disabled

### 2. **APK Fixes (CODE READY, NEEDS BUILD 🔨)**

#### ConnectionManager.kt
- **Fixed:** Device metrics not being sent (all null values)
- **Added:** `DeviceHealthMonitor` injection via Hilt
- **Change:** Now collects all metrics before each heartbeat:
  - Battery level & charging status
  - WiFi SSID & signal strength
  - RAM usage (used/total)
  - Storage usage (used/total)
  - CPU temperature
  - Network type (WiFi/Cellular)
- **Impact:** CMS will display full device telemetry

#### AppViewModel.kt - Persistent Device ID
- **Fixed:** Duplicate devices appearing after uninstall/reinstall
- **Change:** Replaced `UUID.randomUUID()` with `Settings.Secure.ANDROID_ID`
- **Impact:** Same device will always use same identifier, preventing duplicates

#### AppViewModel.kt - Auto-Resume Heartbeat
- **Fixed:** Heartbeat not starting after app restart
- **Added:** `registerOrResumeDevice()` function that:
  - Checks for existing saved token
  - If token exists, resumes heartbeat immediately
  - If no token, registers new device
- **Impact:** Heartbeat starts automatically on app launch without user action

#### AppViewModel.kt - Auth Error Monitoring
- **Fixed:** Device not handling deletion from CMS UI gracefully
- **Added:** `monitorAuthErrors()` function that:
  - Monitors API responses for 401/403 errors
  - Auto-detects when device deleted from backend
  - Automatically re-registers device
- **Impact:** Device auto-recovers from backend deletion

### 3. **Security Improvements (COMPLETED ✅)**

#### docker-compose.prod.yml
- **Fixed:** Remote control relay exposed on 0.0.0.0 (security risk)
- **Removed:** Port mappings for remote-control-relay (3002, 3003)
- **Impact:** Only accessible via internal Docker network, Nginx reverse proxy handles external access

---

## 📱 APK Build Instructions

### Prerequisites
```bash
# Navigate to APK project
cd /home/ubuntu/kiosk/kiosk-touchscreen-app

# Verify git status
git status

# Pull latest changes (if needed)
git pull origin main
```

### Build Steps

#### Option 1: Debug Build (For Testing)
```bash
./gradlew assembleDebug

# APK location:
# app/build/outputs/apk/debug/app-debug.apk
```

#### Option 2: Release Build (For Production)
```bash
# Ensure keystore configured in gradle.properties
./gradlew assembleRelease

# APK location:
# app/build/outputs/apk/release/app-release.apk
```

### Transfer APK to Device
```bash
# Via ADB
adb install -r app/build/outputs/apk/debug/app-debug.apk

# OR copy to shared location
cp app/build/outputs/apk/debug/app-debug.apk /path/to/shared/folder/
```

---

## 🧪 Testing Checklist

### Test Device Information
- **Model:** SAMSUNG SM-A525F
- **Android ID:** `feabbdceecf754b6`
- **Last Tested:** Device ID 72 in database
- **CMS URL:** https://kiosk.mugshot.dev

### Phase 1: Clean Install Test

1. **Uninstall Existing APK**
   ```bash
   adb uninstall id.co.webpro.kiosk.touchscreen.app
   ```

2. **Install New APK**
   ```bash
   adb install app-debug.apk
   ```

3. **Launch App & Register**
   - Open app on device
   - Navigate to Settings → Device Info
   - Note the Device ID displayed (should match Android ID)
   - Trigger registration (if not automatic)

4. **Verify in CMS UI**
   - Login to https://kiosk.mugshot.dev/admin
   - Navigate to Devices/Remotes section
   - Find device with Android ID: `feabbdceecf754b6`
   - **Expected:**
     - Status: Online (green indicator)
     - Last Heartbeat: Recent timestamp (within 30 seconds)
     - Metrics visible:
       - Battery level
       - WiFi SSID & signal
       - RAM usage
       - Storage usage
       - CPU temperature

### Phase 2: Heartbeat Persistence Test

1. **Keep App in Foreground**
   - Wait 2 minutes
   - Check CMS: Last Heartbeat should update every 30 seconds

2. **Move App to Background**
   - Press Home button
   - Wait 3 minutes
   - Check CMS: Last Heartbeat should update every 90 seconds

3. **Lock Device**
   - Lock screen
   - Wait 10 minutes
   - Check CMS: Last Heartbeat should update every 5 minutes (doze mode)

4. **Verify Status**
   - Device should remain "Online" throughout
   - If grace period (5min) exceeded, status may show "Disconnected" but should reconnect

### Phase 3: App Restart Test

1. **Force Close App**
   ```bash
   adb shell am force-stop id.co.webpro.kiosk.touchscreen.app
   ```

2. **Reopen App**
   - Launch app from device

3. **Verify Auto-Resume**
   - **Expected:** Heartbeat resumes immediately without re-registration
   - Check CMS: Device should show "Online" within 30 seconds
   - No duplicate device entry should appear

### Phase 4: Device Deletion Recovery Test

1. **Delete Device from CMS**
   - In CMS UI, delete the device entry
   - **Expected:** Soft delete (not permanent)

2. **Wait for Next Heartbeat**
   - APK will receive 401/403 error
   - **Expected:** APK auto-detects auth error

3. **Verify Auto Re-registration**
   - **Expected:** APK automatically re-registers
   - New token saved
   - Heartbeat resumes
   - Device reappears in CMS UI (same Android ID)

### Phase 5: Metrics Validation Test

1. **Battery Test**
   - Unplug device (if charging)
   - Check CMS: Battery % should match device
   - Plug device back in
   - Check CMS: Charging status should update

2. **Network Test**
   - Ensure connected to WiFi
   - Check CMS: WiFi SSID should display
   - (Optional) Switch to mobile data
   - Check CMS: Network type should change

3. **Resource Test**
   - Open several apps to consume RAM
   - Check CMS: RAM usage should increase
   - Close apps
   - Check CMS: RAM usage should decrease

---

## 🐛 Troubleshooting

### Device Shows "Disconnected"

**Check APK Logs:**
```bash
adb logcat | grep -i "heartbeat\|connection"
```

**Look for:**
- `"Sending heartbeat to CMS"` - Heartbeat being sent
- `"Heartbeat successful"` - Backend accepting heartbeat
- `"Heartbeat failed"` - Network/auth error
- `"Should reconnect: false"` - Backend telling APK to stop (should not happen with fixes)

**Check Backend Response:**
```bash
curl -X POST https://kiosk.mugshot.dev/api/devices/heartbeat \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "battery_percentage": 85,
    "is_charging": false,
    "wifi_ssid": "TestNetwork",
    "wifi_signal_strength": -45,
    "ram_usage_bytes": 2147483648,
    "ram_total_bytes": 4294967296,
    "storage_usage_bytes": 10737418240,
    "storage_total_bytes": 32212254720,
    "cpu_temperature_celsius": 45.5,
    "network_type": "WiFi"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "remote_control_enabled": true,
    "should_reconnect": true,
    "reconnect_delay_seconds": null
  }
}
```

**If `should_reconnect: false`:**
- Check database: `docker exec cosmic-mariadb mysql -u cms -pcms_secure_password cmsdb -e "SELECT id, should_reconnect FROM remotes WHERE device_identifier = 'feabbdceecf754b6';"`
- If database shows `0`, manually fix: `UPDATE remotes SET should_reconnect = 1 WHERE device_identifier = 'feabbdceecf754b6';`
- Restart app containers: `docker compose restart cosmic-app-1 cosmic-app-2 cosmic-app-3`

### Duplicate Devices Appearing

**Root Cause:** Old APK still installed (uses random UUID)

**Solution:**
1. Fully uninstall old APK: `adb uninstall id.co.webpro.kiosk.touchscreen.app`
2. Clear app data: `adb shell pm clear id.co.webpro.kiosk.touchscreen.app` (if app still exists)
3. Install new APK
4. Delete old entries from CMS UI

### Heartbeat Not Starting

**Check APK Code Execution:**
```bash
adb logcat | grep "registerOrResumeDevice"
```

**Expected:**
- `"Found existing token, resuming heartbeat"` - Auto-resume working
- OR `"No existing token, registering device"` - New registration

**If no logs:**
- AppViewModel not initialized
- Check MainActivity/entry point calls `viewModel.registerOrResumeDevice()`

### Metrics Showing Null

**Check Permissions:**
- Battery stats: Should be automatic
- WiFi SSID: Requires `ACCESS_FINE_LOCATION` permission (Android 9+)
- Storage: Requires `READ_EXTERNAL_STORAGE` permission

**Grant Permissions:**
```bash
adb shell pm grant id.co.webpro.kiosk.touchscreen.app android.permission.ACCESS_FINE_LOCATION
```

### Backend Not Reloading Code

**Restart App Containers:**
```bash
cd /home/ubuntu/kiosk
docker compose restart cosmic-app-1 cosmic-app-2 cosmic-app-3
```

**Clear Caches:**
```bash
docker exec cosmic-app-1 php artisan cache:clear
docker exec cosmic-app-1 php artisan config:clear
docker exec cosmic-app-1 php artisan route:clear
```

**Verify Code Changes:**
```bash
# Check DeviceHeartbeatService.php line 142
docker exec cosmic-app-1 grep -A 5 "should_reconnect" /var/www/html/app/Services/DeviceHeartbeatService.php
```

---

## 📊 Database Verification

### Check Device Status
```bash
docker exec cosmic-mariadb mysql -u cms -pcms_secure_password cmsdb -e "
SELECT 
  id,
  device_identifier,
  name,
  status,
  should_reconnect,
  heartbeat_interval_seconds,
  last_heartbeat_received_at,
  created_at,
  deleted_at
FROM remotes 
WHERE device_identifier = 'feabbdceecf754b6'
ORDER BY created_at DESC 
LIMIT 3;
"
```

### Check Recent Heartbeats
```bash
docker exec cosmic-mariadb mysql -u cms -pcms_secure_password cmsdb -e "
SELECT 
  id,
  battery_percentage,
  wifi_ssid,
  wifi_signal_strength,
  ram_usage_bytes,
  should_reconnect,
  last_heartbeat_received_at
FROM remotes 
WHERE device_identifier = 'feabbdceecf754b6'
AND deleted_at IS NULL;
"
```

### Manually Set should_reconnect (If Needed)
```bash
docker exec cosmic-mariadb mysql -u cms -pcms_secure_password cmsdb -e "
UPDATE remotes 
SET should_reconnect = 1 
WHERE device_identifier = 'feabbdceecf754b6';
"
```

---

## ✅ Success Criteria

### All Tests Must Pass:
- [ ] Device registers with persistent Android ID (no duplicates)
- [ ] Device shows "Online" status in CMS UI within 30 seconds
- [ ] Last Heartbeat timestamp updates automatically
- [ ] All metrics visible in CMS (battery, WiFi, RAM, storage, CPU temp)
- [ ] Heartbeat persists across app restarts (no re-registration)
- [ ] Heartbeat continues in background and doze mode
- [ ] Device auto-recovers from deletion (re-registers automatically)
- [ ] Backend returns `should_reconnect: true` in heartbeat response
- [ ] No duplicate devices after uninstall/reinstall

---

## 📝 Additional Notes

### Heartbeat Intervals
- **Foreground:** 30 seconds
- **Background:** 90 seconds  
- **Doze Mode:** 300 seconds (5 minutes)
- **Grace Period:** 300 seconds (5 minutes) - Device marked "Disconnected" if no heartbeat within this period

### Grace Period Behavior
- After grace period, device status changes to "Disconnected"
- `should_reconnect` remains `true` in database
- Next heartbeat automatically sets status back to "Online"
- This is normal behavior and not a bug

### Remote Control Relay
- **Internal URL:** http://remote-control-relay:3002 (Docker network)
- **External URL:** https://kiosk.mugshot.dev/remote-relay-ws (Nginx proxy)
- No direct port exposure for security

### Code Repository
- **APK:** `kiosk-touchscreen-app/` directory
- **Backend:** `cosmic-media-streaming-dpr/` directory
- **Deployment Scripts:** Root directory
- **Documentation:** `doc/` directory

---

## 🚀 Next Steps After Testing

1. **If All Tests Pass:**
   - Build release APK with signing key
   - Deploy to all kiosk devices
   - Monitor production logs for 24-48 hours
   - Document any edge cases discovered

2. **If Tests Fail:**
   - Collect APK logs: `adb logcat > apk-logs.txt`
   - Collect backend logs: `docker logs cosmic-app-1 > backend-logs.txt`
   - Share logs for debugging
   - Check each troubleshooting step systematically

3. **Production Rollout:**
   - Test on 1-2 devices first
   - Verify metrics collection accurate
   - Gradually roll out to all devices
   - Keep old APK backup in case rollback needed

---

**Last Updated:** February 2, 2026  
**Backend Fixes:** Deployed & Active ✅  
**APK Status:** Ready to build 🔨  
**Test Device:** SAMSUNG SM-A525F (ID: 72)