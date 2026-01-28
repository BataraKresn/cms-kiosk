# Architecture Explanation: Remote Control System

## Remote-Control-Relay (WebSocket Server)
**Location**: `/home/ubuntu/kiosk/remote-control-relay/`
**Type**: Backend Service (Node.js)
**Port**: 3002 (HTTP), 3003 (WebSocket)
**Function**: 
- Acts as "middleman" / "relay server" between Android devices and CMS viewers
- Manages WebSocket rooms (1 device = 1 room)
- Broadcasts video frames from device to all viewers in that room
- Forwards input commands from viewers to the device
- Does NOT control devices directly - just relays data

**Analogy**: Like a telephone switchboard operator connecting callers

## Remote-Control-Device (Android APK Services)
**Location**: `/home/ubuntu/kiosk/kiosk-touchscreen-app/app/src/main/java/.../data/services/`
**Type**: Android Services (Kotlin)
**Components**:
1. **ScreenCaptureService.kt** - Captures device screen
2. **InputInjectionService.kt** - Receives and executes touch/keyboard commands
3. **RemoteControlWebSocketClient.kt** - Connects to relay server

**Function**:
- Runs ON the Android device (kiosk)
- Captures screen and sends frames to relay
- Receives input commands from relay and executes them
- The "controlled" side of the system

**Analogy**: Like the phone on one end of the call

## Data Flow

```
┌─────────────────┐         WebSocket         ┌──────────────────────┐
│   Android APK   │ ◄─────────────────────────┤  Remote-Control-     │
│   (Device)      │         ws://3003         │  Relay Server        │
│                 ├──────────────────────────►│  (Node.js)           │
│ • Screen        │  Sends: Video frames      │                      │
│   Capture       │  Receives: Input commands │  • Manages rooms     │
│ • Input         │                           │  • Broadcasts data   │
│   Injection     │                           │  • Authenticates     │
└─────────────────┘                           └──────────────────────┘
                                                         ▲
                                                         │ WebSocket
                                                         │ ws://3003
                                                         │
                                              ┌──────────┴───────────┐
                                              │   CMS Viewer UI      │
                                              │   (Browser)          │
                                              │                      │
                                              │ • Canvas display     │
                                              │ • Click/touch input  │
                                              │ • Keyboard input     │
                                              └──────────────────────┘
```

## Key Differences

| Aspect              | Remote-Control-Relay          | Remote-Control-Device        |
|---------------------|-------------------------------|------------------------------|
| **Location**        | Server (Docker container)     | Android APK                  |
| **Language**        | Node.js / JavaScript          | Kotlin / Android             |
| **Role**            | Router / Relay                | Screen source / Input target |
| **Scalability**     | 1 relay serves many devices   | 1 service per device         |
| **Connections**     | Many-to-many (N devices, M viewers) | One-to-one with relay  |
| **State**           | Stateless (just routes data)  | Stateful (device state)      |

## Why Separate?

**Separation Benefits:**
1. **Scalability**: One relay can handle hundreds of devices
2. **Network efficiency**: Direct device-to-viewer would require complex NAT traversal
3. **Security**: Devices don't expose ports directly to internet
4. **Load balancing**: Can add multiple relay servers if needed
5. **Monitoring**: Centralized point to track all sessions

## Existing vs New System

**Old System (VNC-based):**
```
Android APK ──► DroidVNC-NG (3rd party) ──► Browser (VNC client)
```

**New System (Custom):**
```
Android APK ──► Remote-Control-Relay ──► CMS Viewer UI
  (Our code)      (Our code)               (Our code)
```

**Advantages of new system:**
- No third-party dependencies
- Integrated with CMS authentication
- Session recording capability
- Permission-based access control
- Better performance (optimized for our use case)
