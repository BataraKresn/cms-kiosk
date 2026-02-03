# APK Remote Control Integration - Status & Action Plan

## 🔴 MASALAH UTAMA

Device status "Connected" di database ≠ Remote Control active!

### Yang Terjadi Sekarang:
- ✅ APK `ConnectionManager` kirim heartbeat ke `/api/devices/heartbeat`
- ✅ Database update `remotes.status = 'Connected'`
- ❌ APK **TIDAK ADA** Remote Control code (WebSocket relay client)
- ❌ Viewer page menunjukkan "Disconnected" karena device tidak connect ke relay

### Root Cause:
**Remote Control code masih di `/remote-control-poc/` (POC folder), belum diintegrate ke APK production di `/kiosk-touchscreen-app/`**

---

## 📁 FILES YANG PERLU DI-COPY KE APK

### 1. Core Services (dari `/remote-control-poc/android/`)
```
RemoteControlWebSocketClient.kt    → app/src/main/java/.../data/services/
InputInjectionService.kt           → app/src/main/java/.../data/services/
ScreenCaptureService.kt           → app/src/main/java/.../data/services/
```

### 2. ViewModel (dari guide/implementation docs)
```
RemoteControlViewModel.kt         → app/src/main/java/.../presentation/remotecontrol/
```

### 3. UI Screen (SUDAH DIBUAT tapi belum ada ViewModel!)
```
RemoteControlScreen.kt            → ✅ SUDAH ADA di presentation/remotecontrol/
```

### 4. Navigation (SUDAH DIUPDATE)
```
Route.kt                          → ✅ SUDAH ADA Route.AppRemoteControl
App.kt                            → ✅ SUDAH ADA composable route
SettingsView.kt                   → ✅ SUDAH ADA button
```

---

## 🎯 ACTION PLAN

### Option A: Full Integration (Recommended)
Copy semua Remote Control code dari POC ke APK production:

1. **Copy Services**
   ```bash
   cp remote-control-poc/android/RemoteControlWebSocketClient.kt \
      kiosk-touchscreen-app/app/src/main/java/com/kiosktouchscreendpr/cosmic/data/services/
   
   cp remote-control-poc/android/ScreenCaptureService.kt \
      kiosk-touchscreen-app/app/src/main/java/com/kiosktouchscreendpr/cosmic/data/services/
   
   cp remote-control-poc/android/InputInjectionService.kt \
      kiosk-touchscreen-app/app/src/main/java/com/kiosktouchscreendpr/cosmic/data/services/
   ```

2. **Create ViewModel** (menggunakan template dari guide)

3. **Update AndroidManifest.xml** - tambahkan permissions:
   - `FOREGROUND_SERVICE`
   - `FOREGROUND_SERVICE_MEDIA_PROJECTION`
   - `BIND_ACCESSIBILITY_SERVICE`

4. **Update Dependencies** di `build.gradle`:
   - OkHttp WebSocket
   - Hilt dependency injection

5. **Rebuild & Deploy APK**

### Option B: Mock Device Test (Quick Test)
Buat mock device script untuk testing relay tanpa APK:

```javascript
// mock-device.js - connect as device, send test frames
const WebSocket = require('ws');

const ws = new WebSocket('wss://kiosk.mugshot.dev/remote-control-ws');

ws.on('open', () => {
    // Auth as device
    ws.send(JSON.stringify({
        type: 'auth',
        role: 'device',
        token: '8yvL3wK7y6ZM7lqfUlpjWm8zenImQ0hnDLDuDScaSWgYgv0hj73ORP80ZGW0Qw',
        deviceId: '74'
    }));
});

ws.on('message', (data) => {
    const msg = JSON.parse(data.toString());
    console.log('Received:', msg.type);
    
    if (msg.type === 'auth_success') {
        console.log('✅ Authenticated as device');
        
        // Send test frame every 500ms
        setInterval(() => {
            ws.send(JSON.stringify({
                type: 'frame',
                data: 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', // 1x1 red pixel
                quality: 80,
                timestamp: Date.now()
            }));
        }, 500);
    }
});
```

Run: `node mock-device.js` → akan kirim frame test ke viewer

---

## 🚨 IMMEDIATE SOLUTION

**Untuk test SEKARANG tanpa integrate full APK:**

1. Jalankan mock device script (Option B)
2. Refresh viewer page
3. Canvas akan menunjukkan frame (walaupun test frame)

**Untuk production REAL:**

1. Copy semua files dari POC ke APK (Option A)
2. Build APK baru
3. Install di device
4. Start Remote Control dari Settings → akan connect ke relay dengan benar

---

## 📋 CHECKLIST

### Backend (✅ DONE)
- [x] Relay server running
- [x] WebSocket endpoint working
- [x] Device authentication working
- [x] Viewer authentication working
- [x] Frame routing working
- [x] Input command routing working
- [x] Handle device_status messages

### APK UI (✅ DONE)
- [x] RemoteControlScreen.kt implemented
- [x] Navigation integrated
- [x] Settings button added

### APK Core (❌ MISSING - BLOCKER)
- [ ] RemoteControlWebSocketClient.kt → NOT IN PRODUCTION APK
- [ ] ScreenCaptureService.kt → NOT IN PRODUCTION APK
- [ ] InputInjectionService.kt → NOT IN PRODUCTION APK
- [ ] RemoteControlViewModel.kt → NOT IN PRODUCTION APK
- [ ] AndroidManifest permissions → NOT ADDED
- [ ] build.gradle dependencies → NOT ADDED

---

## 💡 KESIMPULAN

**Remote Control feature:**
- Backend relay server: ✅ 100% ready
- CMS viewer page: ✅ 100% ready
- APK UI Screen: ✅ 100% ready
- APK Core Code: ❌ 0% integrated (still in POC folder)

**Next step:** Choose Option A (full integration) or Option B (mock test first)

