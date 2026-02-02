# APK Heartbeat Troubleshooting Guide

## 🔴 Problem: Device Disconnected After Initial Registration

**Symptoms:**
- Device registered successfully (metrics show in CMS)
- Status remains "Disconnected"
- Heartbeat stops after 1-2 minutes
- CPU temp shows NULL

---

## 🔍 Diagnosis Steps

### 1. Check Device Status in Database
```bash
docker exec platform-db-prod mysql -u kiosk_platform -p5Kh3dY82ry05 platform -e "
SELECT 
  id, 
  status, 
  should_reconnect, 
  last_heartbeat_received_at, 
  TIMESTAMPDIFF(SECOND, last_heartbeat_received_at, NOW()) as seconds_ago,
  cpu_temp
FROM remotes 
WHERE id = 74;
"
```

### 2. Check if Heartbeat is Coming In (Monitor Real-time)
```bash
# Watch backend logs for device 74
docker logs -f --tail 20 cosmic-app-1-prod 2>&1 | grep -E "device_id.*74|Heartbeat processed"
```

### 3. Monitor From Device (if ADB connected)
```bash
# Monitor APK logs
adb logcat | grep -E "\[REGISTER\]|ConnectionManager|Heartbeat|DeviceRegistration"
```

---

## 🚑 Quick Fixes

### Fix 1: Force Restart Heartbeat from UI

**Di device Android:**
1. Force close app: Settings → Apps → Kiosk App → Force Stop
2. Clear cache (optional): Settings → Apps → Kiosk App → Storage → Clear Cache
3. Reopen app

**Expected:** App will call `registerOrResumeDevice()` → Detect existing token → Resume heartbeat

### Fix 2: Manual Heartbeat Test (Verify Backend Works)

```bash
# Get device token
TOKEN=$(docker exec platform-db-prod mysql -u kiosk_platform -p5Kh3dY82ry05 platform -N -e "SELECT token FROM remotes WHERE id = 74;")

# Send manual heartbeat
curl -X POST https://kiosk.mugshot.dev/api/devices/heartbeat \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "battery_level": 100,
    "wifi_strength": -45,
    "screen_on": true,
    "storage_available_mb": 80000,
    "storage_total_mb": 120000,
    "ram_usage_mb": 3328,
    "ram_total_mb": 7489,
    "cpu_temp": 35.5,
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

If this works → Problem is in APK, not backend.

### Fix 3: Reduce Grace Period (More Aggressive Timeout)

```bash
# Set grace period to 60 seconds
docker exec platform-db-prod mysql -u kiosk_platform -p5Kh3dY82ry05 platform -e "
UPDATE remotes 
SET grace_period_seconds = 60 
WHERE id = 74;
"
```

### Fix 4: Check APK Permissions

**Required permissions in AndroidManifest.xml:**
- `INTERNET` - For API calls
- `ACCESS_NETWORK_STATE` - For network monitoring
- `ACCESS_WIFI_STATE` - For WiFi info
- `ACCESS_FINE_LOCATION` - For WiFi SSID (Android 9+)
- `REQUEST_IGNORE_BATTERY_OPTIMIZATIONS` - For background heartbeat

Grant location permission:
```bash
adb shell pm grant id.co.webpro.kiosk.touchscreen.app android.permission.ACCESS_FINE_LOCATION
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Heartbeat Stops After App Goes to Background

**Cause:** Android battery optimization kills background tasks

**Solution:**
1. Open Android Settings
2. Apps → Kiosk App → Battery
3. Select "Unrestricted" or "Don't optimize"

### Issue 2: WiFi Signal Too Weak (-100 dBm)

**Cause:** Device too far from router or weak signal

**Impact:** Network requests fail/timeout

**Solution:**
- Move device closer to WiFi router
- Check WiFi signal: Should be > -70 dBm
- Use WiFi analyzer app to check signal strength

### Issue 3: CPU Temperature NULL

**Cause:** Android doesn't expose CPU temp on all devices

**Solution:** This is NORMAL. Not all Android devices allow reading CPU temperature. Other metrics will still work.

**Check if device supports it:**
```bash
adb shell ls /sys/class/thermal/
```

If no `thermal_zone*` folders → CPU temp not available on this device.

### Issue 4: StateFlow Not Collecting (UI Not Showing)

**Cause:** `App()` composable not collecting `appViewModel.state`

**Diagnosis:**
- Open app
- Check if logcat shows: `[REGISTER] Starting device registration`
- If NO logs → StateFlow not collected

**Solution:** Already implemented in code, but verify in App.kt:
```kotlin
val state = appViewModel.state.collectAsStateWithLifecycle()
```

### Issue 5: Network Timeout During Heartbeat

**Symptoms:** Heartbeat stops after 3-5 attempts

**Cause:** 
- Poor WiFi signal
- Backend slow/overloaded
- Timeout too short (15s default)

**Solution:**
Check network latency:
```bash
# From device
adb shell ping -c 5 kiosk.mugshot.dev

# From server
ping -c 5 <DEVICE_IP>
```

---

## 📊 Expected Behavior (When Working)

### Foreground (App visible)
- Heartbeat every **30 seconds**
- Status should be "Connected" within 1 minute
- All metrics updated

### Background (App hidden)
- Heartbeat every **90 seconds** (adjusted for battery)
- Status may briefly show "Temporarily Offline" (normal)
- Should reconnect within grace period

### Doze Mode (Screen off, idle)
- Heartbeat every **5 minutes**
- Status will show "Disconnected" after 5-10 minutes
- Will reconnect when screen turns on

---

## 🔧 Advanced Debugging

### Enable Verbose Logging

Add to AppViewModel.kt:
```kotlin
init {
    Log.d(TAG, "AppViewModel initialized")
}
```

Check if ViewModel is created:
```bash
adb logcat | grep "AppViewModel initialized"
```

### Monitor Network Traffic

```bash
# Watch all HTTP requests from device
adb logcat | grep -E "HttpClient|Ktor|okhttp"
```

### Check Battery Optimization Status

```bash
adb shell dumpsys deviceidle whitelist | grep kiosk
```

If not whitelisted → Battery optimization is enabled (bad for background tasks)

---

## ✅ Success Criteria

- ✅ Device status: "Connected" (green checkmark)
- ✅ Last Seen: Updates every 30-90 seconds
- ✅ Battery: 100%
- ✅ WiFi: -45 dBm or better (not -100)
- ✅ RAM: Shows actual usage
- ✅ Storage: Shows actual space
- ✅ CPU: May be NULL (device limitation)
- ✅ Backend logs: "Heartbeat processed successfully" every 30s

---

## 🆘 If Nothing Works

1. **Completely uninstall APK:**
   ```bash
   adb uninstall id.co.webpro.kiosk.touchscreen.app
   ```

2. **Delete device from CMS UI** (Soft Delete)

3. **Reinstall fresh APK:**
   ```bash
   adb install app-debug.apk
   ```

4. **Open app and wait 2 minutes**

5. **Check CMS UI** - Device should appear as "Connected"

If still disconnected after this → Share:
- Screenshot of CMS UI
- Output of: `docker logs --tail 50 cosmic-app-1-prod`
- Output of: `adb logcat | grep AppViewModel` (first 50 lines after app open)
