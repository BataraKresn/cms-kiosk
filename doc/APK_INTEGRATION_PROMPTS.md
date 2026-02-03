## APK Integration Prompts

### 🎯 SITUATION ANALYSIS

**Current State:**
- ✅ Backend: Remote Control relay server fully working and tested
- ✅ CMS Frontend: Remote control viewer page functional
- ✅ Device Registration: APK can register and get token
- ✅ Connection Manager: APK heartbeat working to CMS backend
- ❌ Missing: APK doesn't connect to relay server for video/input streaming

**APK Code Status:**
- ✅ RemoteControlWebSocketClient: Already fully implemented
- ✅ Authentication: Token handling correct
- ✅ Frame transmission: Ready to send JPEG frames
- ✅ Input handling: Ready to receive and process commands
- ⚠️ Not hooked up: Needs to be called from UI screen

---

### 📋 PROMPT TEMPLATES FOR APK DEVELOPER

#### PROMPT #1: Basic Integration (Entry Point)
```
I have a Kotlin Android app with:
- RemoteControlViewModel (already handles connection)
- RemoteControlWebSocketClient (already sends frames)
- Device token and ID from registration

I need to add a Remote Control Screen (Jetpack Compose or XML) that:
1. Shows device connection status (Disconnected, Connecting, Connected, Error)
2. Shows live screen feed (received as base64 JPEG frames)
3. Handles touch/swipe input to send to device

What's the minimal code to:
- Start remote control: viewModel.startRemoteControl(token, relayUrl)
- Receive frames from WebSocket
- Display them in an ImageView or Canvas
- Capture touch events and send coordinates to relay

Show complete code for the Screen/Activity with state management.
```

#### PROMPT #2: Deep Dive (For Full Implementation)
```
I'm implementing Remote Device Screen Capture and Input Control using:
- Relay Server: wss://kiosk.mugshot.dev/remote-control-ws
- Architecture: CMS Viewer (Web) ↔ Relay ↔ Android Device

My RemoteControlWebSocketClient is ready. I need to implement the UI layer.

Requirements:
1. Screen that initializes remote control with deviceId, token, relayUrl
2. Display live JPEG frames as they arrive (base64 encoded in JSON)
3. Gesture handling (tap, swipe, pinch) → send as input_command JSON
4. Connection status indicator with auto-reconnect info
5. Show FPS and latency metrics
6. Handle device disconnection gracefully

The connection flow is:
- APK connects to relay with role='device'
- Relay confirms auth_success
- APK receives input_command messages
- APK sends periodic frames (base64 JSON)

Create complete Jetpack Compose Screen that handles all of this with proper state management and error handling.
```

#### PROMPT #3: For Existing Screen Update
```
I have a RemoteControl screen in my app but it's using legacy approach.

Current issues:
- Uses old WebSocket library instead of Ktor
- Manual JSON parsing instead of data classes
- No proper state management
- Doesn't handle lifecycle properly

I have RemoteControlViewModel with these methods ready:
- startRemoteControl(context, deviceId, token, relayUrl)
- stopRemoteControl()

Collected states:
- remoteControlState: StateFlow<RemoteControlState>
- connectionStatus: StateFlow<ConnectionStatus>

Can you refactor my RemoteControl screen to:
1. Use the ViewModel instead of direct WebSocket calls
2. Collect state flows and react to state changes
3. Use LaunchedEffect/DisposableEffect for lifecycle
4. Display proper loading/error/success states
5. Handle orientation changes properly
6. Clean up on screen dismiss

Show the complete refactored Compose screen.
```

#### PROMPT #4: Testing and Debugging
```
I'm having issues with the Remote Control feature on my Android app.

Background:
- Device successfully registers and gets token
- Heartbeat works (200ms latency)
- BUT: Device doesn't connect to relay server for streaming

I have:
- Device ID: 74
- Device Token: 8yvL3wk7y6ZM7lqfUipiWm5zen1mQhnhDLDuDScaSWgTgv0hj7r3ORP9DZGW0Qwp
- Relay URL: wss://kiosk.mugshot.dev/remote-control-ws

Questions:
1. How to verify connection is being attempted (check logs)?
2. How to mock/test without real device?
3. What should I look for in logcat to troubleshoot?
4. How to add better error logging to RemoteControlWebSocketClient?

Show me:
- Enhanced logging I should add
- Diagnostic code to verify connection stages
- How to test with mock relay locally
```

#### PROMPT #5: Screen Capture Service Integration
```
I have:
- RemoteControlViewModel with startRemoteControl()
- RemoteControlWebSocketClient that sends frames

I need to integrate with ScreenCaptureService (already exists in app).

Currently:
- ScreenCaptureService captures screen to JPEG
- But doesn't know about RemoteControl feature

I need:
1. ScreenCaptureService to detect when RemoteControl is active
2. Send captured frames to RemoteControlWebSocketClient.sendFrame(bytes)
3. Respect quality/FPS adjustments from relay server
4. Handle app going background (stop capture)
5. Handle battery optimization (reduce FPS in battery saver)

Show complete implementation for:
- Callback interface between services
- Lifecycle management
- Frame queue and transmission
- Settings adjustment handling
```

#### PROMPT #6: For UI/UX Specialist
```
I need to design a Remote Device Control Screen for my CMS.

The backend API provides:
- Device status: Connected/Disconnected
- Live screen feed as JPEG frames (~5-10 FPS)
- Real-time latency metrics
- Battery/temperature/WiFi info from device

On the viewer (desktop browser) side:
- User sees live device screen
- Can tap/click to send input
- Can swipe/drag to simulate gestures
- Shows FPS counter and latency
- Shows device status with auto-reconnect info

Design requirements:
- Responsive layout (work on mobile/tablet/desktop)
- Handle network lag gracefully
- Show clear error states
- Provide clear feedback for user actions
- Display device health metrics

Create:
1. Figma-compatible wireframe description
2. HTML/CSS/JS implementation for the viewer
3. Touch/mouse input handling code
4. Real-time metrics display

Focus on UX for poor network conditions (high latency, packet loss).
```

---

### 🔧 SPECIFIC CODE PATTERNS

#### Pattern #1: Start Remote Control
```kotlin
// This is what the APK screen should call
viewModel.startRemoteControl(
    context = this,
    deviceId = "74",
    authToken = "8yvL3wk7y6ZM7lqfUipiWm5zen1mQhnhDLDuDScaSWgTgv0hj7r3ORP9DZGW0Qwp",
    relayServerUrl = "wss://kiosk.mugshot.dev/remote-control-ws"
)

// Monitor connection
viewModel.connectionStatus.collect { status ->
    when (status) {
        is ConnectionStatus.Connected -> showVideo()
        is ConnectionStatus.Error -> showError(status.message)
        is ConnectionStatus.Disconnected -> showStatus("Disconnected")
    }
}
```

#### Pattern #2: Frame Reception
```kotlin
// RemoteControlViewModel receives frames like this:
val incomingFrames: Flow<ByteArray> = webSocketClient.frameFlow
    .map { frameJson -> 
        // frameJson = { type: "frame", format: "jpeg", data: "base64...", timestamp: 123 }
        Base64.getDecoder().decode(frameJson.data)
    }

// UI should display them
incomingFrames.collect { jpegBytes ->
    val bitmap = BitmapFactory.decodeByteArray(jpegBytes, 0, jpegBytes.size)
    imageView.setImageBitmap(bitmap)
}
```

#### Pattern #3: Input Sending
```kotlin
// When user touches screen
touchEvent.x.toFloat() // 0-1080
touchEvent.y.toFloat() // 0-2400

// Send to relay
webSocketClient.sendInputCommand(
    type = "touch",
    x = (event.x / screenWidth * 1080).toInt(),
    y = (event.y / screenHeight * 2400).toInt()
)

// Relay forwards to device
// Device receives: { type: "input_command", command: { type: "touch", x, y } }
// InputInjectionService processes and executes
```

---

### ✅ WHAT'S READY IN BACKEND

| Component | Status | Details |
|-----------|--------|---------|
| Device Registration API | ✅ | `/api/devices/register` returns token |
| Heartbeat API | ✅ | `/api/devices/heartbeat` updates status |
| Relay Server | ✅ | Accepts device/viewer connections |
| Device Auth | ✅ | Token-based, database verified |
| Viewer Auth | ✅ | Session-based + remote_permissions |
| Frame Routing | ✅ | WebSocket → viewers |
| Input Routing | ✅ | WebSocket → InputInjectionService |
| Auto-reconnect | ✅ | Exponential backoff implemented |

---

### 🚀 NEXT STEPS FOR APK

**Priority 1: UI Screen**
- Create RemoteControlScreen (Compose or XML)
- Wire up ViewModel
- Display connection status
- Show live frames

**Priority 2: Input Handling**
- Touch event detection
- Coordinate transformation
- Gesture recognition
- Send to relay server

**Priority 3: Frame Display**
- Base64 JPEG decoding
- Canvas/ImageView rendering
- Frame queue management
- Latency measurement

**Priority 4: Optimization**
- Frame rate adjustment
- Quality based on network
- Battery optimization
- Memory management

---

### 📞 QUESTIONS TO ASK DEVELOPER

1. **UI Framework**: Are you using Jetpack Compose or XML layouts?
2. **Screen State**: Do you have existing Remote Control screen or building from scratch?
3. **Target Devices**: What's minimum Android version?
4. **Network Conditions**: Are you on WiFi or cellular?
5. **Performance**: Target FPS? Quality? Battery priority?
6. **Testing**: Do you have test device or need mock testing?

---

### 🔗 RELATED FILES

- APK ViewModel: `/kiosk-touchscreen-app/app/src/main/java/.../RemoteControlViewModel.kt`
- WebSocket Client: `/kiosk-touchscreen-app/app/src/main/java/.../RemoteControlWebSocketClient.kt`
- Connection Manager: `/kiosk-touchscreen-app/app/src/main/java/.../ConnectionManager.kt`
- Relay Server: `/remote-control-relay/server.js`
- Test Proof: Mock device successfully connected to relay
