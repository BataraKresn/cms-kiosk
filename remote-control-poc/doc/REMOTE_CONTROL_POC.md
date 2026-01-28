# 🎮 Custom Android Remote Control - Proof of Concept (POC)

> **Project**: Cosmic Kiosk Remote Control System  
> **Version**: 1.0.0 (POC)  
> **Date**: January 28, 2026  
> **Status**: Documentation & Prototype Phase

---

## 📋 Executive Summary

### Objective
Develop a **custom, self-hosted remote control solution** for Android kiosk devices that allows administrators to view and control kiosk screens directly from the CMS web interface, without relying on third-party tools like VNC, TeamViewer, or AnyDesk.

### Key Requirements
- ✅ **Internet-based access** (HTTPS/WSS only)
- ✅ **Embedded in CMS** (no new window/tab)
- ✅ **Full kiosk control** (touch, swipe, keyboard)
- ✅ **Screen streaming** from Android device
- ✅ **Custom Android agent** APK
- ✅ **Self-hosted** infrastructure

### Technology Stack

| Component | Technology | Purpose |
|-----------|-----------|---------|
| **Android APK** | Kotlin, MediaProjection, AccessibilityService | Screen capture + Input injection |
| **Relay Server** | Node.js/Python + WebSocket/WebRTC | Stream relay & command routing |
| **CMS Frontend** | Laravel Blade + Vue.js/Alpine.js | Viewer interface |
| **Database** | MariaDB | Session & permission management |
| **Protocol** | WebSocket (Phase 1), WebRTC (Phase 2) | Real-time communication |

---

## 🏗️ Architecture Overview

### System Components

```
┌──────────────────────────────────────────────────────────────────┐
│                        Internet (HTTPS/WSS)                       │
└──────────────────────────────────────────────────────────────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    │              │              │
                    ▼              ▼              ▼
         ┌─────────────────┐  ┌──────────────┐  ┌──────────────┐
         │  CMS Web App    │  │ Relay Server │  │Android Kiosk │
         │  (Laravel)      │  │ (Node.js)    │  │    Device    │
         ├─────────────────┤  ├──────────────┤  ├──────────────┤
         │ • Viewer UI     │  │ • WebSocket  │  │ • MediaProj. │
         │ • Auth/Perms    │  │   Hub        │  │ • Input Svc  │
         │ • WS Client     │  │ • Stream     │  │ • Encoder    │
         │ • Input Sender  │  │   Relay      │  │ • WS Client  │
         └─────────────────┘  └──────────────┘  └──────────────┘
                    │              │              │
                    └──────────────▼──────────────┘
                         ┌─────────────────┐
                         │   MariaDB       │
                         │ • Devices       │
                         │ • Sessions      │
                         │ • Permissions   │
                         └─────────────────┘
```

### Data Flow

#### **1. Screen Streaming (Android → CMS)**
```
Android Device                    Relay Server              CMS Viewer
     │                                 │                         │
     │ 1. Capture Screen              │                         │
     │    (MediaProjection)           │                         │
     │                                 │                         │
     │ 2. Encode to MJPEG/H.264       │                         │
     │                                 │                         │
     │ 3. Send via WebSocket          │                         │
     ├────────────────────────────────>│                         │
     │                                 │                         │
     │                                 │ 4. Relay to Viewer     │
     │                                 ├────────────────────────>│
     │                                 │                         │
     │                                 │                    5. Display
     │                                 │                      on Canvas
```

#### **2. Input Control (CMS → Android)**
```
CMS Viewer                       Relay Server             Android Device
     │                                 │                         │
     │ 1. User Click/Swipe            │                         │
     │                                 │                         │
     │ 2. Send Input Command          │                         │
     ├────────────────────────────────>│                         │
     │                                 │                         │
     │                                 │ 3. Route to Device     │
     │                                 ├────────────────────────>│
     │                                 │                         │
     │                                 │              4. Inject Touch
     │                                 │            (AccessibilityService)
```

---

## 🎯 POC Scope & Phases

### Phase 1: Minimal Viable POC (2 weeks)

**Goal**: Prove the concept with basic screen streaming

**Features**:
- ✅ Android screen capture (MediaProjection)
- ✅ MJPEG encoding (simple, fast)
- ✅ WebSocket streaming
- ✅ Basic viewer (canvas display)
- ✅ One-way stream (view only)

**Deliverables**:
- Android service classes (boilerplate)
- WebSocket relay server
- CMS viewer page (prototype)
- Database schema
- Documentation

### Phase 2: Input Control (2 weeks)

**Goal**: Add bi-directional control

**Features**:
- ✅ AccessibilityService integration
- ✅ Touch event injection
- ✅ Swipe gestures
- ✅ Keyboard input
- ✅ Bi-directional WebSocket

### Phase 3: Production Ready (3-4 weeks)

**Goal**: Optimize for production

**Features**:
- ✅ H.264 encoding (better quality)
- ✅ WebRTC migration (adaptive streaming)
- ✅ Authentication & authorization
- ✅ Session management
- ✅ Recording capability
- ✅ Multi-viewer support
- ✅ Performance optimization

---

## 📱 Android APK Components

### Required Services

#### 1. **ScreenCaptureService**
```kotlin
Purpose: Capture device screen using MediaProjection API
Features:
- Real-time screen capture (30 FPS)
- MJPEG/H.264 encoding
- Resolution scaling (1080p → 720p)
- Frame rate control
```

#### 2. **InputInjectionService**
```kotlin
Purpose: Inject touch/keyboard events using AccessibilityService
Features:
- Touch event simulation
- Swipe gesture recognition
- Long press support
- Keyboard input
- Multi-touch support (future)
```

#### 3. **RemoteControlWebSocketClient**
```kotlin
Purpose: Maintain persistent connection with relay server
Features:
- Send video frames
- Receive input commands
- Auto-reconnection
- Network change handling
- Heartbeat mechanism
```

### Required Permissions

```xml
<!-- AndroidManifest.xml additions -->
<uses-permission android:name="android.permission.SYSTEM_ALERT_WINDOW" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE_MEDIA_PROJECTION" />

<service
    android:name=".services.ScreenCaptureService"
    android:foregroundServiceType="mediaProjection"
    android:exported="false" />
    
<service
    android:name=".services.InputInjectionService"
    android:permission="android.permission.BIND_ACCESSIBILITY_SERVICE"
    android:exported="true">
    <intent-filter>
        <action android:name="android.accessibilityservice.AccessibilityService" />
    </intent-filter>
    <meta-data
        android:name="android.accessibilityservice"
        android:resource="@xml/accessibility_service_config" />
</service>
```

---

## 🖥️ Relay Server Architecture

### Option 1: Node.js WebSocket Server (Recommended for POC)

```javascript
Features:
- ws library (lightweight)
- Simple pub/sub pattern
- Room-based routing (kiosk_id)
- TypeScript support
```

### Option 2: Python FastAPI WebSocket

```python
Features:
- Already using FastAPI in remote-android-device
- Easy integration with existing codebase
- async/await support
```

### Server Responsibilities

1. **Connection Management**
   - Register Android devices (publishers)
   - Register CMS viewers (subscribers)
   - Maintain device → viewer mapping

2. **Stream Routing**
   - Forward video frames: Android → Viewers
   - Forward input commands: Viewers → Android
   - Handle disconnections & cleanup

3. **Session Management**
   - Track active sessions
   - Log connections/disconnections
   - Store in database

---

## 🎨 CMS Viewer UI

### Page Structure

```php
Route: /back-office/remotes/{id}/control

Components:
1. Canvas Element (screen display)
2. Control Toolbar (home, back, keyboard)
3. Status Indicator (connected/disconnected)
4. Session Info (device name, IP, uptime)
5. Recording Controls (start/stop)
```

### UI Mockup

```
┌────────────────────────────────────────────────────────────┐
│  Cosmic CMS - Remote Control: Kiosk Device #123            │
├────────────────────────────────────────────────────────────┤
│  [◀ Back]  [⌂ Home]  [⌨ Keyboard]  [🔴 Record]  [✕ Close] │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────┐  │
│  │                                                       │  │
│  │                                                       │  │
│  │          📱 Device Screen Canvas                     │  │
│  │                 (1080x1920)                          │  │
│  │                                                       │  │
│  │          Click to interact with device               │  │
│  │                                                       │  │
│  │                                                       │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                             │
│  Status: 🟢 Connected | FPS: 28 | Latency: 45ms           │
│  Device: Kiosk-Lobby-01 | IP: 192.168.1.100               │
└────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema

### New Tables

#### 1. **remote_sessions**
```sql
Purpose: Track active and historical remote control sessions
Fields:
- id (PK)
- remote_id (FK → remotes)
- user_id (FK → users)
- started_at
- ended_at
- duration_seconds
- status (active, ended, error)
- viewer_ip
- relay_server_id
```

#### 2. **remote_permissions**
```sql
Purpose: Control who can access which devices
Fields:
- id (PK)
- user_id (FK → users)
- remote_id (FK → remotes)
- can_view (boolean)
- can_control (boolean)
- can_record (boolean)
- created_at
```

#### 3. **remote_recordings** (Optional)
```sql
Purpose: Store session recordings metadata
Fields:
- id (PK)
- session_id (FK → remote_sessions)
- file_path
- file_size_mb
- duration_seconds
- format (webm, mp4)
- created_at
```

### Modified Tables

#### **remotes** (existing)
```sql
New fields:
- remote_control_enabled (boolean, default: false)
- remote_control_port (integer, default: 5555)
- screen_resolution (string, e.g., "1080x1920")
- last_frame_at (timestamp, nullable)
```

---

## 🔐 Security Considerations

### Authentication & Authorization

1. **Device Authentication**
   ```
   - Each device has unique token (existing)
   - Token validated on WebSocket connection
   - Rejected if token invalid/disabled
   ```

2. **User Authorization**
   ```
   - Check remote_permissions table
   - Verify can_view/can_control flags
   - Role-based access (Admin, Manager, Viewer)
   ```

3. **Connection Encryption**
   ```
   - WSS (WebSocket Secure) required
   - TLS 1.3 minimum
   - Certificate validation
   ```

### Rate Limiting

```javascript
// Relay server
const rateLimiter = {
  maxFramesPerSecond: 30,
  maxInputCommandsPerSecond: 100,
  maxConcurrentViewers: 5  // per device
};
```

---

## 📊 Performance Targets

### POC Phase 1

| Metric | Target | Acceptable |
|--------|--------|------------|
| Frame Rate | 30 FPS | 20 FPS |
| Latency (input) | < 100ms | < 200ms |
| Bandwidth | < 2 Mbps | < 5 Mbps |
| CPU Usage (Android) | < 20% | < 30% |
| Battery Impact | < 10%/hour | < 15%/hour |

### Production Phase 3

| Metric | Target | Acceptable |
|--------|--------|------------|
| Frame Rate | 30 FPS | 25 FPS |
| Latency (input) | < 50ms | < 100ms |
| Bandwidth | < 1 Mbps (adaptive) | < 3 Mbps |
| Concurrent Viewers | 5 per device | 3 per device |
| Uptime | 99.9% | 99% |

---

## 🧪 Testing Strategy

### POC Validation Tests

1. **Connectivity Test**
   - Android connects to relay server ✓
   - CMS viewer connects to relay server ✓
   - Bi-directional communication works ✓

2. **Screen Streaming Test**
   - Android captures screen ✓
   - Frames sent to relay ✓
   - Viewer receives and displays frames ✓
   - Frame rate acceptable (20-30 FPS) ✓

3. **Input Control Test**
   - Touch event sent from viewer ✓
   - Relay routes to correct device ✓
   - AccessibilityService injects event ✓
   - UI responds correctly ✓

4. **Network Resilience Test**
   - Handle WiFi disconnect/reconnect ✓
   - Auto-reconnection works ✓
   - Session recovery after network change ✓

---

## 📚 Implementation Roadmap

### Week 1-2: Android APK + Relay Server

**Days 1-3**: Android Services
- Create ScreenCaptureService boilerplate
- Implement MediaProjection capture
- MJPEG encoding
- WebSocket client (send frames)

**Days 4-7**: Relay Server
- Setup WebSocket server (Node.js)
- Implement room-based routing
- Handle device/viewer connections
- Test end-to-end streaming

**Days 8-10**: Input Control
- Create InputInjectionService
- AccessibilityService setup
- Touch injection logic
- WebSocket receive commands

### Week 3-4: CMS Integration + Testing

**Days 11-14**: CMS Viewer UI
- Create Filament page
- Canvas element + WebSocket client
- Display video frames
- Input event handlers

**Days 15-18**: Database & Sessions
- Create migrations
- Session tracking logic
- Permission checks
- Audit logging

**Days 19-21**: Testing & Documentation
- End-to-end testing
- Performance benchmarking
- Security audit
- Documentation updates

---

## 🚀 Quick Start Guide

### 1. Setup Relay Server

```bash
# Create new service
cd /home/ubuntu/kiosk
mkdir remote-control-relay
cd remote-control-relay

# Initialize Node.js project
npm init -y
npm install ws express dotenv

# Copy boilerplate code (generated separately)
# Start server
node server.js
```

### 2. Build Android APK

```bash
# Add new services to existing APK
cd /home/ubuntu/kiosk/kiosk-touchscreen-dpr-app

# Copy boilerplate services (generated separately)
# app/src/main/java/.../services/ScreenCaptureService.kt
# app/src/main/java/.../services/InputInjectionService.kt
# app/src/main/java/.../services/RemoteControlWebSocketClient.kt

# Update AndroidManifest.xml (see boilerplate)
# Build APK
./gradlew assembleDebug
```

### 3. Database Migration

```bash
# Copy migration files to Laravel
cd /home/ubuntu/kiosk/cosmic-media-streaming-dpr

# Run migrations
docker compose exec cosmic-app php artisan migrate
```

### 4. Deploy CMS Viewer

```bash
# Copy viewer page files
# app/Filament/Resources/RemoteResource/Pages/RemoteControlViewer.php
# resources/views/filament/pages/remote-control-viewer.blade.php
# resources/js/remote-control-viewer.js

# Build assets
npm run build
```

---

## 💡 Alternative Approaches

### Option A: WebRTC (Better Quality)

**Pros:**
- Lower latency (P2P possible)
- Adaptive bitrate
- Built-in codecs
- Better for production

**Cons:**
- More complex signaling
- NAT traversal issues
- Steeper learning curve

**When to use**: Phase 3 (production)

### Option B: Screen Recording + Playback

**Pros:**
- Simpler implementation
- No real-time complexity

**Cons:**
- High latency (not suitable)
- Large storage requirements

**When to use**: Recording feature only

### Option C: Hybrid VNC + Custom

**Pros:**
- Leverage existing VNC protocol
- Mature ecosystem

**Cons:**
- Still depends on DroidVNC-NG
- Not fully custom

**When to use**: NOT recommended (defeats purpose)

---

## 📖 References & Resources

### Android APIs
- [MediaProjection Documentation](https://developer.android.com/reference/android/media/projection/MediaProjection)
- [AccessibilityService Guide](https://developer.android.com/guide/topics/ui/accessibility/service)
- [Android Gestures](https://developer.android.com/training/gestures)

### Networking
- [WebSocket API](https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API)
- [WebRTC for Android](https://webrtc.org/getting-started/android)

### Similar Projects (Open Source)
- [scrcpy](https://github.com/Genymobile/scrcpy) - Screen mirroring (ADB-based)
- [QtScrcpy](https://github.com/barry-ran/QtScrcpy) - Qt-based scrcpy GUI
- [Android Screen Monitor](https://github.com/adakoda/android-screen-monitor)

---

## ✅ Success Criteria

### POC is Successful If:

1. ✅ Screen appears in CMS viewer (acceptable FPS)
2. ✅ Click on viewer triggers touch on Android
3. ✅ No third-party apps required (fully custom)
4. ✅ Works over internet (HTTPS/WSS)
5. ✅ Embedded in CMS (no new tab)
6. ✅ Basic session tracking works
7. ✅ Code is maintainable & documented

---

## 🎯 Next Steps

After reading this POC documentation, proceed to:

1. **[Implementation Guide](./REMOTE_CONTROL_IMPLEMENTATION_GUIDE.md)** - Step-by-step implementation
2. **[Android Boilerplate](../remote-control-poc/android/)** - Service classes
3. **[Relay Server Code](../remote-control-poc/relay-server/)** - WebSocket server
4. **[Database Migrations](../remote-control-poc/migrations/)** - Schema files
5. **[CMS Viewer UI](../remote-control-poc/cms-viewer/)** - Frontend prototype

---

**Document Version**: 1.0.0  
**Last Updated**: January 28, 2026  
**Author**: Cosmic Development Team  
**Status**: ✅ Ready for Implementation
