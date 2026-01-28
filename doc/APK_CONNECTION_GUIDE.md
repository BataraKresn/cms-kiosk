# 📱 Android APK Remote Control Connection Guide

## Flow: CMS → Relay Server → Android APK

### Prerequisites

1. **Android APK installed on kiosk device**
2. **Device registered in CMS** (remotes table)
3. **Remote control enabled** for that device
4. **Relay server running** (port 3003)
5. **Accessibility Service enabled** on Android

---

## Step-by-Step Connection Process

### 1️⃣ Prepare Database (Enable Remote Control)

```sql
-- Login to database
docker compose -f docker-compose.prod.yml exec mariadb mysql -ukiosk_platform -p5Kh3dY82ry05 platform

-- Enable remote control on device
UPDATE remotes 
SET 
    remote_control_enabled = 1,
    remote_control_port = 5555,
    status = 'Connected'
WHERE name = 'Kiosk Lobby' OR id = 1;

-- Verify
SELECT id, name, status, remote_control_enabled, remote_control_port 
FROM remotes 
WHERE remote_control_enabled = 1;
```

### 2️⃣ Android APK Configuration

**Before building APK**, set env.properties:

```properties
# /home/ubuntu/kiosk/kiosk-touchscreen-app/env.properties

APP_PASSWORD=your_kiosk_password
WS_URL=ws://YOUR_SERVER_IP:3003
WEBVIEW_BASEURL=http://YOUR_SERVER_IP
```

**Important**: Replace `YOUR_SERVER_IP` with actual IP address!

### 3️⃣ Build Android APK

```bash
cd /home/ubuntu/kiosk/kiosk-touchscreen-app

# Build debug APK
./gradlew assembleDebug

# Output location:
# app/build/outputs/apk/debug/app-debug.apk
```

### 4️⃣ Install APK on Android Device

```bash
# Via ADB
adb install -r app/build/outputs/apk/debug/app-debug.apk

# Or manually:
# - Copy APK to device via USB
# - Enable "Unknown Sources" in Settings
# - Install APK
```

### 5️⃣ Enable Accessibility Service on Android

**Manual Steps on Android Device:**
1. Open **Settings**
2. Go to **Accessibility**
3. Find **"Cosmic Remote Control"**
4. Toggle **ON**
5. Accept permission dialog

**Why needed?**: Accessibility service allows app to inject touch and keyboard input.

### 6️⃣ Start Remote Control from Android

**Option A: Automatic (via ViewModel)**
```kotlin
// In your Activity/Composable
val viewModel: RemoteControlViewModel = hiltViewModel()

// Start remote control
viewModel.startRemoteControl(
    context = context,
    deviceId = "1", // From remotes table
    authToken = "your-auth-token",
    relayServerUrl = "ws://YOUR_SERVER_IP:3003"
)

// Request screen capture permission (one-time)
viewModel.requestScreenCapturePermission(activity, REQUEST_CODE)
```

**Option B: Manual Service Start**
```kotlin
// Start services manually
val intent = Intent(context, ScreenCaptureService::class.java)
context.startForegroundService(intent)
```

### 7️⃣ Connect from CMS (Browser)

1. Open browser: `http://YOUR_SERVER_IP/admin`
2. Login with credentials
3. Navigate to **Management > Remotes**
4. Find your device (status = "Connected", remote_control_enabled = true)
5. Click **"Remote Control"** button
6. New tab opens with viewer

**Expected behavior:**
- Canvas shows loading...
- WebSocket connects to `ws://YOUR_SERVER_IP:3003`
- Screen frames appear from Android device
- You can click/touch on canvas to control device

---

## Connection Flow Diagram

```
┌──────────────────────────────────────────────────────────────┐
│                    Connection Sequence                        │
└──────────────────────────────────────────────────────────────┘

1. Android APK starts
   └─► RemoteControlWebSocketClient connects to ws://SERVER:3003
       └─► Sends: { type: "auth", deviceId: "1", token: "..." }

2. Relay Server
   └─► Authenticates against database (remotes table)
   └─► Creates room for device
   └─► Responds: { type: "auth-success" }

3. Android ScreenCaptureService
   └─► Captures screen (MediaProjection API)
   └─► Encodes frames as MJPEG
   └─► Sends to relay: { type: "frame", data: base64_image }

4. Admin opens CMS viewer
   └─► Browser connects to ws://SERVER:3003
       └─► Sends: { type: "viewer-join", deviceId: "1" }

5. Relay Server
   └─► Adds viewer to device room
   └─► Starts broadcasting frames: viewer receives frames

6. Admin clicks on canvas
   └─► JavaScript sends: { type: "input", command: "touch", x: 100, y: 200 }

7. Relay Server
   └─► Forwards to Android device

8. Android InputInjectionService
   └─► Receives input command
   └─► Executes via AccessibilityService
   └─► Touch injected at coordinates
```

---

## Verification Checklist

### ✅ Check Relay Server Running
```bash
curl http://localhost:3002/health
# Should return: {"status":"ok","uptime":...,"rooms":0}
```

### ✅ Check Android Connecting
```bash
# Watch relay server logs
docker logs -f remote-relay-prod

# Look for:
# "Device 1 authenticated"
# "Device 1 joined room"
```

### ✅ Check WebSocket from Browser
Open browser console (F12) on CMS viewer page:
```javascript
// Should see:
WebSocket connection opened
Authenticated as viewer for device 1
Received frame: ...
```

### ✅ Check Database Session
```sql
SELECT * FROM remote_sessions 
WHERE status = 'active' 
ORDER BY started_at DESC 
LIMIT 1;
```

---

## Troubleshooting

### ❌ Android can't connect to relay
**Solution:**
- Check WS_URL in env.properties
- Ensure port 3003 is open: `nc -zv SERVER_IP 3003`
- Check firewall: `sudo ufw status`

### ❌ "Authentication failed"
**Solution:**
- Verify deviceId exists in remotes table
- Check remote_control_enabled = 1
- Check token/auth logic in WebSocketClient

### ❌ No frames appearing in viewer
**Solution:**
- Grant MediaProjection permission on Android
- Check ScreenCaptureService is running
- Check Android logs: `adb logcat | grep ScreenCapture`

### ❌ Touch not working
**Solution:**
- Enable Accessibility Service manually
- Check InputInjectionService is running
- Check Android logs: `adb logcat | grep InputInjection`

### ❌ "WebSocket connection failed" in browser
**Solution:**
- Check relay server is running: `docker ps | grep relay`
- Check port 3003 accessible from browser machine
- Try WS instead of WSS (if no SSL)

---

## Testing Commands

```bash
# 1. Check all containers
docker ps --format "table {{.Names}}\t{{.Status}}"

# 2. Test relay health
curl http://localhost:3002/health

# 3. Check active rooms
curl http://localhost:3002/stats

# 4. Watch relay logs
docker logs -f remote-relay-prod

# 5. Watch Android logs (if connected via ADB)
adb logcat -s RemoteControl:* ScreenCapture:* InputInjection:*

# 6. Test WebSocket from command line
npm install -g wscat
wscat -c ws://localhost:3003
```

---

## Production Checklist

- [ ] env.properties has production SERVER_IP
- [ ] Firewall allows port 3003
- [ ] SSL/WSS configured (for production)
- [ ] Database has device entry
- [ ] remote_control_enabled = 1
- [ ] APK built with release config
- [ ] Accessibility service enabled on device
- [ ] MediaProjection permission granted
- [ ] Relay server monitoring enabled
