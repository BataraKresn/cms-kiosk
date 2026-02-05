# NPM + Cloudflare WebSocket Setup Guide
**Remote Control Relay Configuration for Cloudflare → NPM → Cosmic Server**

---

## 🔧 NPM (Nginx Proxy Manager) Configuration - WebSocket Relay

### SETUP DI NPM (via Web UI):

#### 1️⃣ PROXY HOST UTAMA (sudah ada?)
```
Domain: kiosk.mugshot.dev
Forward to: <IP_SERVER>:8080
✅ Block Common Exploits: ON
✅ Websockets Support: ON ← PENTING!
✅ SSL: Force SSL, HTTP/2, HSTS
```

#### 2️⃣ TAMBAH CUSTOM LOCATION untuk WebSocket Relay

**Proxy Host:** `kiosk.mugshot.dev` → Edit  
**Tab:** Custom Locations  

**✚ Add Location**
```
Define Location: /remote-control-ws
Scheme: http
Forward Hostname/IP: <IP_SERVER>
Forward Port: 8080
✅ Websockets Support: ON
```

**Advanced Tab:**
```nginx
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "upgrade";
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;

# WebSocket timeouts
proxy_connect_timeout 7d;
proxy_send_timeout 7d;
proxy_read_timeout 7d;

# No buffering
proxy_buffering off;
proxy_request_buffering off;
```

---

## ☁️ CLOUDFLARE SETTINGS

### 3️⃣ Cloudflare Dashboard → kiosk.mugshot.dev
```
✅ SSL/TLS Mode: Full (Strict) atau Full
✅ WebSockets: ON (biasanya default ON)
✅ Always Use HTTPS: ON
```

---

## 🌐 FLOW YANG BENAR

```
APK/CMS → wss://kiosk.mugshot.dev/remote-control-ws (port 443)
    ↓
Cloudflare (SSL termination, WebSocket proxy)
    ↓
NPM (reverse proxy) - http://NPM_IP:80/remote-control-ws
    ↓
Cosmic Server (internal) - http://SERVER_IP:8080/remote-control-ws
    ↓
Nginx (internal) - proxy to remote-relay-prod:3003
    ↓
Relay Server (WebSocket handler)
```

---

## 🧪 TESTING

### Test dari browser console / wscat:
```javascript
const ws = new WebSocket('wss://kiosk.mugshot.dev/remote-control-ws');
ws.onopen = () => console.log('✅ Connected');
ws.onerror = (e) => console.error('❌ Error:', e);
```

### Atau via CLI:
```bash
wscat -c wss://kiosk.mugshot.dev/remote-control-ws
```

---

## 🐛 TROUBLESHOOTING

### ❌ 502 Bad Gateway
- NPM tidak bisa reach server internal
- Check IP/port di NPM config

### ❌ 404 Not Found
- Custom location belum ditambah
- Atau path salah (/remote-control-ws)

### ❌ Connection timeout
- Firewall block port 8080
- Server cosmic down

### ❌ WebSocket handshake failed
- WebSocket support tidak di-enable di NPM
- Upgrade headers tidak di-forward

---

---

# 🔐 CLOUDFLARE SSL MODE: FLEXIBLE

**✅ RECOMMENDED untuk setup: Cloudflare → NPM → Internal Server**

## FLOW dengan Flexible Mode

```
APK/CMS
    ↓ wss://kiosk.mugshot.dev/remote-control-ws (HTTPS/WSS - Encrypted)
Cloudflare (SSL Termination)
    ↓ ws://NPM_IP/remote-control-ws (HTTP/WS - Plain) ← Cloudflare downgrade
NPM (Nginx Proxy Manager)
    ↓ http://SERVER_IP:8080/remote-control-ws (HTTP - Plain)
Cosmic Server (Nginx Internal)
    ↓ ws://remote-relay-prod:3003 (WebSocket)
Relay Server
```

---

## ✅ KEUNTUNGAN MODE FLEXIBLE

- ✅ NPM tidak perlu SSL certificate (lebih simple)
- ✅ Server internal tidak perlu SSL (sudah di-handle Cloudflare)
- ✅ WebSocket tetap work (wss → ws downgrade otomatis)
- ✅ User/APK tetap pakai wss:// (secure dari sisi mereka)
- ✅ Setup cepat, maintenance mudah

---

## ⚠️ KEKURANGAN (minor)

**⚠️ Traffic Cloudflare → NPM tidak encrypted**
- Tapi biasanya OK jika NPM di private network/internal
- Atau gunakan VPN/tunnel untuk extra security

---

## 🔧 SETUP NPM untuk Flexible Mode

### 1️⃣ NPM Proxy Host Settings:
```
Domain: kiosk.mugshot.dev
Scheme: http (bukan https!) ← PENTING
Forward Hostname/IP: <SERVER_INTERNAL_IP>
Forward Port: 8080
✅ Websockets Support: ON
❌ SSL: OFF (Cloudflare yang handle)
```

### 2️⃣ Custom Location: /remote-control-ws
```
Define Location: /remote-control-ws
Scheme: http
Forward Hostname/IP: <SERVER_INTERNAL_IP>
Forward Port: 8080
✅ Websockets Support: ON
```

**Advanced:**
```nginx
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "upgrade";
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;

proxy_connect_timeout 7d;
proxy_send_timeout 7d;
proxy_read_timeout 7d;

proxy_buffering off;
proxy_request_buffering off;
```

---

## ☁️ CLOUDFLARE SETTINGS (Flexible Mode)

### Dashboard → kiosk.mugshot.dev → SSL/TLS:
```
✅ SSL/TLS encryption mode: Flexible
✅ Always Use HTTPS: ON
✅ Automatic HTTPS Rewrites: ON
```

### Network Tab:
```
✅ WebSockets: ON (default ON)
✅ HTTP/2: ON
✅ HTTP/3 (with QUIC): Optional
```

---

## 🧪 TESTING

### Dari browser console atau wscat:
```javascript
const ws = new WebSocket('wss://kiosk.mugshot.dev/remote-control-ws');
ws.onopen = () => console.log('✅ Connected');
ws.onerror = (e) => console.error('❌ Error:', e);
```

### Expected result:
```
✅ WebSocket connection opened
✅ Relay log: "📱 Device added to room" (dari APK)
✅ Relay log: "👁️ Viewer added to room" (dari CMS)
✅ Relay log: "📹 Broadcasting frame" (saat streaming)
```

---

## 🐛 TROUBLESHOOTING FLEXIBLE MODE

### ❌ "Too many redirects" loop:
- NPM proxy host jangan enable "Force SSL"
- Cloudflare "Always Use HTTPS" cukup di Cloudflare side

### ❌ WebSocket connection failed:
- Check "Websockets Support" ON di NPM
- Check Cloudflare WebSocket setting ON
- Check firewall allow port 8080

### ❌ 502 Bad Gateway:
- NPM tidak bisa reach server internal
- Check IP address dan port 8080 accessible
- Check docker container running

### ❌ APK masih tidak connect:
- APK logcat cek error detail
- Pastikan APK pakai `wss://kiosk.mugshot.dev/remote-control-ws`
- Pastikan deviceId + token valid di database

---

## 📋 CHECKLIST IMPLEMENTASI

### Di NPM:
- [ ] Edit proxy host `kiosk.mugshot.dev`
- [ ] Set scheme: `http` (bukan https)
- [ ] Enable "Websockets Support"
- [ ] Tambah custom location `/remote-control-ws`
- [ ] Enable "Websockets Support" di custom location
- [ ] Paste advanced config (headers, timeout, no buffering)
- [ ] Test: Save & Apply

### Di Cloudflare:
- [ ] Set SSL/TLS mode: **Flexible**
- [ ] Enable "Always Use HTTPS"
- [ ] Verify "WebSockets" ON
- [ ] Test DNS resolution

### Di Server Cosmic:
- [ ] Verify nginx internal config ada `/remote-control-ws`
- [ ] Verify relay server running (`docker ps | grep relay`)
- [ ] Verify relay health: `curl http://localhost:3002/health`
- [ ] Check firewall allow port 8080

### Testing:
- [ ] Browser console test WebSocket connection
- [ ] APK connect test
- [ ] CMS viewer connect test
- [ ] Check relay logs untuk device + viewer
- [ ] Verify frame streaming

---

## 🎯 QUICK START (5 menit)

1. **NPM:** Edit proxy host → Custom location `/remote-control-ws` → Paste advanced config
2. **Cloudflare:** SSL mode = Flexible
3. **Test:** Browser console → `new WebSocket('wss://kiosk.mugshot.dev/remote-control-ws')`
4. **Verify:** Check relay logs → `docker logs remote-relay-prod`

Selesai! 🚀

---

**Created:** February 5, 2026  
**Version:** 1.0  
**Last Updated:** February 5, 2026
