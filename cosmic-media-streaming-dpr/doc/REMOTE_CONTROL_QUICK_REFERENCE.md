# 🎮 Remote Control - Quick Implementation Reference

## 🚀 Quick Start

### 1. Load the JavaScript Modules

```html
<!-- In your Blade template -->
<script src="{{ asset('js/connection-state-manager.js') }}"></script>
<script src="{{ asset('js/remote-control-viewer.js') }}"></script>
```

### 2. Create HTML Elements

```html
<!-- Required DOM elements (see enhanced template for full HTML) -->
<div id="device-screen"></div>
<div id="connection-status"></div>
<div id="loading-overlay"></div>
<div id="disconnected-overlay"></div>
<div id="reconnecting-overlay"></div>
<div id="error-message"></div>
<div id="reconnect-countdown"></div>
<div id="reconnect-attempt"></div>

<!-- Control buttons -->
<button id="btn-back"></button>
<button id="btn-home"></button>
<button id="btn-keyboard"></button>
<button id="btn-disconnect"></button>
<button id="btn-retry"></button>
<button id="btn-cancel-reconnect"></button>
```

### 3. Configure and Initialize

```javascript
// Set global configuration
window.remoteControlConfig = {
    wsUrl: 'wss://relay.example.com/remote-control',
    deviceId: 123,
    deviceToken: 'device_token_here',
    deviceName: 'Device Name',
    userId: 456,
    userName: 'User Name',
    sessionToken: 'session_token_here',
    canControl: true,
    canRecord: false
};

// Initialization happens automatically on DOMContentLoaded
// Access viewer via: window.remoteControlViewer
```

---

## 🔄 State Machine Quick Reference

### States

```javascript
ConnectionStateManager.States = {
    DISCONNECTED: 'disconnected',    // No connection
    CONNECTING: 'connecting',        // Attempting initial connect
    CONNECTED: 'connected',          // Active, authenticated
    RECONNECTING: 'reconnecting',    // Auto-retry in progress
    ERROR: 'error'                   // Error state
}
```

### Error Types

```javascript
ConnectionStateManager.ErrorTypes = {
    AUTH_FAILED: 'auth_failed',           // Wrong credentials
    DEVICE_OFFLINE: 'device_offline',     // Device not reachable
    TIMEOUT: 'timeout',                   // No response
    NETWORK_ERROR: 'network_error',       // Network issue
    UNKNOWN: 'unknown'                    // Unknown error
}
```

---

## 💡 Common Code Patterns

### Check if Connected

```javascript
// Check connection state
if (window.remoteControlViewer.connectionManager.isConnected()) {
    console.log('Connected!');
}

// Check if can send commands
if (window.remoteControlViewer.connectionManager.canSendCommands()) {
    sendTouch(x, y);
}

// Get current state
const state = window.remoteControlViewer.connectionManager.getState();
console.log('Current state:', state);
```

### Handle State Changes

```javascript
// Already handled in viewer, but if you need custom logic:
viewer.connectionManager.onStateChange = (state, error) => {
    console.log(`State changed to: ${state}`);
    if (error) {
        console.error('Error:', error.message);
    }
};
```

### Manual Reconnection

```javascript
// Manually trigger reconnection
window.remoteControlViewer.manualReconnect();

// Cancel auto-reconnection
window.remoteControlViewer.cancelReconnect();

// Disconnect completely
window.remoteControlViewer.manualDisconnect();
```

### Send Commands (Protected)

```javascript
// Send touch - automatically checks connection
viewer.sendTouch(0.5, 0.5);  // Normalized coordinates

// Send swipe
viewer.sendSwipe(0.2, 0.2, 0.8, 0.8);

// Send key
viewer.sendKeyPress(3);  // Home key

// Send text
viewer.sendText('Hello World');
```

---

## 🎨 CSS Customization

### Connection Status Colors

```css
.status-dot.connected {
    background: #34c759;  /* Green */
}

.status-dot.connecting {
    background: #ff9f0a;  /* Orange */
}

.status-dot.reconnecting {
    background: #ff9f0a;  /* Orange */
}

.status-dot.disconnected {
    background: #ff3b30;  /* Red */
}

.status-dot.error {
    background: #ff3b30;  /* Red */
}
```

### Overlay Customization

```css
/* Loading overlay */
#loading-overlay {
    background: rgba(0,0,0,0.95);
}

/* Disconnected overlay */
#disconnected-overlay {
    background: rgba(255, 59, 48, 0.05);
}

/* Reconnecting overlay */
#reconnecting-overlay {
    background: rgba(255, 159, 10, 0.05);
}
```

---

## 🔍 Debugging

### Enable Console Logging

All important events are logged to console with emojis:

```
✅ Authentication successful
❌ Authentication failed
🔌 WebSocket connection/disconnection
⚠️ Warnings
🔄 State transitions
🔂 Auto-reconnection
📊 Connection state changes
```

### Access Viewer Instance

```javascript
// From browser console
const viewer = window.remoteControlViewer;

// Check connection manager
console.log(viewer.connectionManager.getState());
console.log(viewer.connectionManager.getErrorMessage());
console.log(viewer.connectionManager.getReconnectStatus());
```

### Check WebSocket Status

```javascript
// From browser console
const viewer = window.remoteControlViewer;
console.log('WebSocket ready state:', viewer.ws?.readyState);
// 0 = CONNECTING, 1 = OPEN, 2 = CLOSING, 3 = CLOSED
```

---

## ⚡ Performance Tips

1. **Reconnect Delays**: Keep default values (3-30 seconds)
2. **Max Attempts**: 5 is reasonable; adjust based on tolerance
3. **Frame Timeout**: 30 seconds is good for frame detection
4. **Button Disabling**: Happens automatically; no extra overhead

---

## 🐛 Troubleshooting

### Issue: Connection hangs on "Connecting..."

**Solution**: Check WebSocket URL and network connectivity
```javascript
// Verify configuration
console.log(window.remoteControlConfig.wsUrl);

// Check WebSocket readyState
console.log(viewer.ws?.readyState);
```

### Issue: Auto-reconnect not working

**Solution**: Check if autoReconnect is enabled
```javascript
// Verify auto-reconnect is on
console.log(viewer.connectionManager.autoReconnect);

// Check if max attempts reached
console.log(viewer.connectionManager.getReconnectStatus());
```

### Issue: Cannot send commands when connected

**Solution**: Check canControl permission
```javascript
console.log(window.remoteControlConfig.canControl);
console.log(viewer.connectionManager.canSendCommands());
```

### Issue: Error message unclear

**Solution**: Check error type and message
```javascript
const error = viewer.connectionManager.lastError;
console.log(error?.type, error?.message);
```

---

## 📊 Monitoring

### Get Connection Stats

```javascript
const viewer = window.remoteControlViewer;

// Get current state
console.log('State:', viewer.connectionManager.getState());

// Get error information
if (!viewer.connectionManager.isConnected()) {
    console.log('Error:', viewer.connectionManager.getErrorMessage());
}

// Get reconnect status
const status = viewer.connectionManager.getReconnectStatus();
console.log('Reconnect Status:', status);
// { isReconnecting, attempt, maxAttempts, countdown, canRetry }

// Get frame statistics
console.log('FPS:', viewer.stats.fps);
console.log('Latency:', viewer.stats.latency);
console.log('Frames Received:', viewer.stats.framesReceived);
```

---

## 🔐 Security Considerations

1. **Always use WSS (WebSocket Secure)** for production
2. **Validate session tokens** on backend
3. **Check user permissions** before rendering controls
4. **Sanitize error messages** shown to users
5. **Rate limit reconnection attempts** on server-side

---

## 📱 Mobile Responsiveness

The template is mobile-responsive:
- Header adapts to smaller screens
- Touch events work on mobile browsers
- Overlays scale properly
- Stats display adjusts layout

---

## 🎯 Next Steps

1. **Customize the HTML template** with your branding
2. **Adjust CSS colors** to match your design system
3. **Configure reconnect parameters** based on your needs
4. **Set up error logging** to a monitoring service
5. **Test all state transitions** thoroughly

---

## 📚 Related Documentation

- [Full Connection State Management Guide](./REMOTE_CONTROL_CONNECTION_STATE_GUIDE.md)
- [Remote Control POC Documentation](./REMOTE_CONTROL_POC.md)
- [Remote Control Architecture](./REMOTE_CONTROL_COMPLETE_FLOW.md)

---

**Version**: 2.0.0  
**Last Updated**: February 4, 2026
