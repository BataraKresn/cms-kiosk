# Remote Control Relay - Testing Tools

Testing scripts untuk Remote Control system tanpa perlu APK fisik.

## 📁 Files

### 1. `test-device-connection.js`
**One-shot connection test** - Test koneksi sekali jalan (10 detik)

**Usage:**
```bash
cd /home/ubuntu/kiosk/remote-control-relay/testing
node test-device-connection.js [deviceId] [token]

# Example:
node test-device-connection.js 74 8yvL3wk7y6ZM7lqfUipiWm5zen1mQhnhDLDuDScaSWgTgv0hj7r3ORP9DZGW0Qwp
```

**What it does:**
- ✅ Connect ke relay sebagai device
- ✅ Authenticate dengan token
- ✅ Kirim 1x heartbeat
- ✅ Kirim 1x frame info
- ✅ Disconnect otomatis setelah 10 detik

**Use case:**
- Quick test relay connection
- Verify authentication working
- Check relay logs for device messages

---

### 2. `mock-device-continuous.js`
**Continuous connection simulator** - Simulasi APK yang tetap connect

**Usage:**
```bash
cd /home/ubuntu/kiosk/remote-control-relay/testing
node mock-device-continuous.js
```

**What it does:**
- ✅ Connect ke relay sebagai device #74
- ✅ Authenticate dengan token
- ✅ **Kirim heartbeat setiap 5 detik** (continuous)
- ✅ Auto-reconnect jika disconnect (max 5x attempts)
- ✅ Receive input_command dari viewer
- ❌ **TIDAK kirim frame** (hanya heartbeat)

**Use case:**
- Simulasi APK yang sedang running
- Test viewer interaction (input commands)
- Test long-running connection stability
- Debug relay message handling

---

## 🎯 Testing Scenarios

### Scenario 1: Test Relay Connection
**Verify relay server dapat menerima device connection**

```bash
# Terminal 1: Monitor relay logs
docker logs -f remote-relay-prod

# Terminal 2: Run test
cd /home/ubuntu/kiosk/remote-control-relay/testing
node test-device-connection.js
```

**Expected logs in relay:**
```
✅ Authenticated: device for device 74
📡 Device 74 status updated to Connected
💓 Device heartbeat from: 74
```

---

### Scenario 2: Test Viewer Interaction
**Verify viewer dapat connect dan menerima device status**

```bash
# Terminal 1: Keep mock device running
cd /home/ubuntu/kiosk/remote-control-relay/testing
node mock-device-continuous.js

# Terminal 2: Open browser
# Navigate to: https://kiosk.mugshot.dev/back-office/remotes/74/remote-control
# Status should show: Connected (not Disconnected)
# Device info should appear
```

**Expected behavior:**
- ✅ Viewer page shows "Connected"
- ✅ Device info displayed (battery, temperature)
- ✅ FPS = 0 (normal - mock tidak kirim frame)
- ✅ Input commands dapat dikirim (cek logs mock-device)

---

### Scenario 3: Test Real APK Connection
**Compare mock vs real APK connection**

```bash
# Step 1: Test with mock device
node mock-device-continuous.js
# Check relay logs → should see "Authenticated: device for device 74"

# Step 2: Stop mock (Ctrl+C)

# Step 3: Start Remote Control di APK
# APK → Settings → Remote Control → Start

# Step 4: Compare relay logs
docker logs -f remote-relay-prod
# Should see same authentication message
# Should see frame messages (not just heartbeat)
```

**If APK works correctly, you should see:**
```
✅ Authenticated: device for device 74
📡 Device 74 status updated to Connected
💓 Device heartbeat from: 74
🎬 Frame received from device 74  <-- THIS is what mock doesn't send
```

---

## 🔧 Troubleshooting

### Issue: "Authentication failed"
**Check:**
- Device ID benar (sesuai database `remotes.id`)
- Token benar (sesuai database `remotes.token`)
- Device `remote_control_enabled = 1` di database

### Issue: "Connection refused"
**Check:**
- Relay server running: `docker ps | grep relay`
- Nginx reverse proxy configured for WebSocket
- Port 3003 accessible

### Issue: Mock connects but APK doesn't
**Check APK:**
- Remote Control code sudah ada di APK
- APK sudah di-rebuild setelah add kode
- APK sudah di-install ulang di device
- BuildConfig.WEBVIEW_BASEURL pointing to correct domain

---

## 📝 Creating More Test Scripts

### Mock Viewer (example)
```javascript
// mock-viewer.js - Simulate CMS viewer
const WebSocket = require('ws');

const ws = new WebSocket('wss://kiosk.mugshot.dev/remote-control-ws');

ws.on('open', () => {
    ws.send(JSON.stringify({
        type: 'auth',
        role: 'viewer',
        deviceId: 74,
        userId: 1,
        sessionToken: 'test-session-token'
    }));
});

ws.on('message', (data) => {
    const msg = JSON.parse(data);
    console.log('Received:', msg.type);
    
    if (msg.type === 'frame') {
        console.log('📹 Frame received:', msg.width, 'x', msg.height);
    }
});
```

### Load Test
```javascript
// load-test.js - Simulate multiple devices
for (let i = 1; i <= 10; i++) {
    setTimeout(() => {
        connectDevice(i);
    }, i * 1000);
}
```

---

## 🚀 Quick Start

**Test relay sekarang:**
```bash
# 1. Pastikan relay running
docker ps | grep relay

# 2. Run continuous mock (keep this running)
cd /home/ubuntu/kiosk/remote-control-relay/testing
node mock-device-continuous.js

# 3. Open browser ke viewer page
# https://kiosk.mugshot.dev/back-office/remotes/74/remote-control

# 4. Status harus "Connected" (bukan "Disconnected")
```

**Expected result:**
- Mock device: Shows "✅ Device authenticated - READY for viewer!"
- Viewer page: Shows "Connected", device info appears
- Relay logs: Shows authentication + periodic heartbeats

---

## 📊 Comparison: Mock vs Real APK

| Feature | Mock Device | Real APK |
|---------|------------|----------|
| WebSocket connection | ✅ Yes | ✅ Yes |
| Authentication | ✅ Yes | ✅ Yes |
| Heartbeat | ✅ Every 5s | ✅ Every 30s |
| **Screen frames** | ❌ **NO** | ✅ **YES** |
| Input command receive | ✅ Yes | ✅ Yes |
| Auto-reconnect | ✅ Yes | ✅ Yes |

**Key difference:** Mock tidak kirim frame, jadi canvas di viewer tetap kosong. Real APK harus kirim JPEG frames untuk tampil di viewer.

---

## 📞 Support

Jika ada issue:
1. Check relay logs: `docker logs remote-relay-prod`
2. Check mock output di terminal
3. Check browser console di viewer page
4. Verify database `remotes` table

