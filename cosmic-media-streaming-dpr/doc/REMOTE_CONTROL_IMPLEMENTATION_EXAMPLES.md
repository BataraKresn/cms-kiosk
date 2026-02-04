# 🎮 Remote Control - Architecture & Implementation Examples

## 🏗️ System Architecture

### High-Level Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CMS Browser                                  │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │          Remote Control Viewer Page                          │  │
│  │                                                               │  │
│  │  ┌────────────────────────────────────────────────────────┐ │  │
│  │  │  RemoteControlViewer Class                             │ │  │
│  │  │  - WebSocket connection management                     │ │  │
│  │  │  - UI state synchronization                            │ │  │
│  │  │  - Input event handling                                │ │  │
│  │  └────────────────────────────────────────────────────────┘ │  │
│  │                        │                                      │  │
│  │  ┌────────────────────▼────────────────────────────────────┐ │  │
│  │  │  ConnectionStateManager                                 │ │  │
│  │  │  - State machine (5 states)                             │ │  │
│  │  │  - Auto-reconnection logic                              │ │  │
│  │  │  - Error tracking                                       │ │  │
│  │  │  - Countdown timers                                     │ │  │
│  │  └────────────────────────────────────────────────────────┘ │  │
│  │                        │                                      │  │
│  │  ┌────────────────────▼────────────────────────────────────┐ │  │
│  │  │  WebSocket Connection                                   │ │  │
│  │  │  (ws://relay-server/remote-control)                     │ │  │
│  │  └────────────────────┬───────────────────────────────────┘ │  │
│  └─────────────────────────┼──────────────────────────────────────┘  │
│                            │                                          │
└────────────────────────────┼──────────────────────────────────────────┘
                             │
        ┌────────────────────▼────────────────────┐
        │                                          │
┌───────▼─────────────────────────────────────────┴──────────┐
│                 Relay Server (Node.js)                       │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Room Manager                                        │  │
│  │  - Manages device ↔ viewer connections              │  │
│  │  - Routes messages between participants             │  │
│  │  - Handles room cleanup                             │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
└────────┬────────────────────────┬──────────────────┬────────┘
         │                        │                  │
    Device 1              Device 2                Device N
   (Android)             (Android)                (Android)
   WebSocket             WebSocket                WebSocket
   Connection           Connection                Connection
```

### Connection Lifecycle

```
┌─────────────────────────────────────────────────────────────────┐
│                    User Opens Viewer Page                        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
                   ┌───────────────────────┐
                   │ DISCONNECTED State    │ (initial)
                   └───────────┬───────────┘
                               │
        Call: viewer.connect()  │
                               ▼
                   ┌───────────────────────┐
                   │ CONNECTING State      │
                   │ - Loading spinner     │
                   │ - Loading message     │
                   └───────────┬───────────┘
                               │
        WebSocket opens        │
                               ▼
                   ┌───────────────────────┐
                   │ Sending auth message  │
                   └───────────┬───────────┘
                               │
           ┌──────────────┬────┴────┬──────────────┐
           │              │         │              │
    Auth Success    Auth Failed  Timeout       Error
           │              │         │              │
           ▼              ▼         ▼              ▼
     ┌─────────┐   ┌─────────┐ ┌────────┐   ┌──────────┐
     │CONNECTED│   │ ERROR   │ │ ERROR  │   │ ERROR    │
     │ State   │   │ State   │ │ State  │   │ State    │
     └────┬────┘   └────┬────┘ └───┬────┘   └─────┬────┘
          │             │           │             │
    Full UI enabled    │        Auto-retry   Auto-retry
          │             │        (after 3s)  (after 3s)
          │         No auto-     │             │
          │         retry       ▼             ▼
          │         (auth err)  RECONNECTING  RECONNECTING
          │                     State         State
          │                        │            │
          │                   Countdown    Countdown
          │                        │            │
          │                   (failed)     (failed)
          │                        │            │
          │                   Max attempts    Max attempts
          │                   reached 5/5     reached 5/5
          │                        │            │
          │                        ▼            ▼
          │                   DISCONNECTED DISCONNECTED
          │                    (allow retry) (allow retry)
          │
     Connection lost
     (device offline, network error)
          │
          ▼
     RECONNECTING State
     with countdown
          │
     ┌─────┴─────┐
     │           │
Reconnect   Max attempts
Succeeds    reached
     │           │
     ▼           ▼
CONNECTED   DISCONNECTED
```

---

## 💻 Implementation Examples

### Example 1: Basic Setup

```javascript
// 1. Include scripts in HTML
<script src="/js/connection-state-manager.js"></script>
<script src="/js/remote-control-viewer.js"></script>

// 2. Configure from backend (in Blade template)
<script>
    window.remoteControlConfig = {
        wsUrl: "{{ $wsUrl }}",
        deviceId: {{ $deviceId }},
        deviceToken: "{{ $deviceToken }}",
        deviceName: "{{ $deviceName }}",
        userId: {{ auth()->id() }},
        sessionToken: "{{ session()->token() }}",
        canControl: {{ auth()->user()->can('control-device') ? 'true' : 'false' }},
    };
</script>

// 3. Initialization happens automatically on page load
// Access viewer: window.remoteControlViewer
```

### Example 2: Custom State Handling

```javascript
// Create custom handlers for state changes
const viewer = window.remoteControlViewer;

// Monitor state changes
viewer.connectionManager.onStateChange = (state, error) => {
    switch (state) {
        case 'connected':
            console.log('✅ Connected - enabling controls');
            enableAllControls();
            notifyServer({ event: 'viewer_connected' });
            break;
            
        case 'disconnected':
            console.log('❌ Disconnected');
            disableAllControls();
            notifyServer({ event: 'viewer_disconnected' });
            break;
            
        case 'reconnecting':
            console.log('🔄 Reconnecting...');
            showNotification('Attempting to reconnect...');
            break;
            
        case 'error':
            console.error('Error:', error?.message);
            showErrorNotification(error?.message);
            logErrorToSentry(error);
            break;
    }
};

// Monitor countdown during reconnection
viewer.connectionManager.onReconnectCountdown = (seconds) => {
    updateCountdownDisplay(seconds);
};

// Track reconnection attempts
viewer.connectionManager.onReconnectAttempt = (attempt) => {
    console.log(`Reconnection attempt ${attempt}/5`);
    analytics.track('remote_control_reconnect_attempt', { attempt });
};

// Handle max attempts reached
viewer.connectionManager.onMaxReconnectAttemptsReached = () => {
    console.log('Max reconnection attempts reached');
    showModal('Connection Failed', 'Unable to reconnect. Please check device status.');
};
```

### Example 3: Enhanced Error Handling

```javascript
// Get detailed error information
function handleConnectionError() {
    const manager = viewer.connectionManager;
    const error = manager.lastError;
    
    // User-friendly messages based on error type
    const messages = {
        'auth_failed': 'Failed to authenticate. Please login again.',
        'device_offline': 'Device is offline. Check if it\'s connected to the network.',
        'timeout': 'Connection timeout. Device may be unresponsive.',
        'network_error': 'Network error. Check your internet connection.',
        'unknown': `Error: ${error?.message || 'Unknown error'}`
    };
    
    const message = messages[error?.type] || messages.unknown;
    
    // Log error for debugging
    console.error('Connection Error:', {
        type: error?.type,
        message: error?.message,
        timestamp: error?.timestamp,
        state: manager.getState(),
        reconnectStatus: manager.getReconnectStatus()
    });
    
    // Show to user
    showErrorDialog(message);
}
```

### Example 4: Manual Reconnection with Validation

```javascript
// Button click handler for manual retry
document.getElementById('btn-retry').addEventListener('click', async () => {
    const viewer = window.remoteControlViewer;
    const manager = viewer.connectionManager;
    
    // Check if device is still online before reconnecting
    const isDeviceOnline = await checkDeviceStatus(viewer.config.deviceId);
    
    if (!isDeviceOnline) {
        showError('Device is offline. Cannot reconnect.');
        return;
    }
    
    // Check if we can still reconnect (not exceeded max attempts)
    if (!manager.getReconnectStatus().canRetry) {
        showError('Maximum reconnection attempts reached.');
        return;
    }
    
    // Trigger manual reconnection
    viewer.manualReconnect();
});

// Helper function to check device status
async function checkDeviceStatus(deviceId) {
    try {
        const response = await fetch(`/api/remotes/${deviceId}/status`);
        const data = await response.json();
        return data.status === 'connected';
    } catch (error) {
        console.error('Error checking device status:', error);
        return false; // Assume offline on error
    }
}
```

### Example 5: Advanced Control Disabling

```javascript
// Automatically disable controls based on connection state
function setupControlStateSync() {
    const viewer = window.remoteControlViewer;
    const manager = viewer.connectionManager;
    
    // Map of control element IDs and their required permissions
    const controls = {
        'btn-back': 'control',
        'btn-home': 'control',
        'btn-keyboard': 'control',
        'canvas': 'control',
        'btn-record': 'record'
    };
    
    // Define permission for current user
    const permissions = {
        control: window.remoteControlConfig.canControl,
        record: window.remoteControlConfig.canRecord
    };
    
    // Update control states on connection changes
    manager.onStateChange = (state) => {
        const isConnected = state === 'connected';
        
        Object.entries(controls).forEach(([elementId, permission]) => {
            const element = document.getElementById(elementId);
            if (!element) return;
            
            const shouldEnable = isConnected && permissions[permission];
            element.disabled = !shouldEnable;
            element.style.opacity = shouldEnable ? '1' : '0.5';
            element.style.pointerEvents = shouldEnable ? 'auto' : 'none';
        });
    };
    
    // Initial state
    manager.onStateChange(manager.getState());
}
```

### Example 6: Performance Monitoring

```javascript
// Track connection performance
function setupPerformanceMonitoring() {
    const viewer = window.remoteControlViewer;
    const manager = viewer.connectionManager;
    const stats = {
        connectionAttempts: 0,
        successfulConnections: 0,
        failedConnections: 0,
        totalDowntime: 0,
        lastConnectTime: null,
        averageLatency: 0
    };
    
    // Track connection attempts
    manager.onReconnectAttempt = (attempt) => {
        stats.connectionAttempts++;
    };
    
    // Track successful connections
    manager.onStateChange = (state) => {
        if (state === 'connected') {
            stats.successfulConnections++;
            stats.lastConnectTime = Date.now();
        } else if (state === 'disconnected' || state === 'error') {
            stats.failedConnections++;
        }
    };
    
    // Track performance metrics
    setInterval(() => {
        const metrics = {
            ...stats,
            currentState: manager.getState(),
            fps: viewer.stats.fps,
            latency: viewer.stats.latency,
            framesReceived: viewer.stats.framesReceived
        };
        
        // Send to analytics
        console.log('Performance metrics:', metrics);
        
        // Could send to external monitoring service
        // sendToMonitoringService(metrics);
    }, 60000); // Every minute
    
    return stats;
}
```

### Example 7: Session Management

```javascript
// Manage viewer session
class RemoteControlSession {
    constructor(viewer) {
        this.viewer = viewer;
        this.startTime = Date.now();
        this.disconnections = [];
        
        this.setupSessionTracking();
    }
    
    setupSessionTracking() {
        const manager = this.viewer.connectionManager;
        
        // Track state changes for session history
        manager.onStateChange = (state, error) => {
            if (state === 'disconnected' || state === 'error') {
                this.disconnections.push({
                    time: Date.now(),
                    state: state,
                    error: error?.type || null
                });
            }
        };
    }
    
    getSessionSummary() {
        return {
            duration: Date.now() - this.startTime,
            framesReceived: this.viewer.stats.framesReceived,
            averageFps: this.viewer.stats.fps,
            averageLatency: this.viewer.stats.latency,
            disconnectionCount: this.disconnections.length,
            disconnections: this.disconnections,
            currentState: this.viewer.connectionManager.getState()
        };
    }
    
    endSession() {
        const summary = this.getSessionSummary();
        console.log('Session Summary:', summary);
        
        // Send to server
        fetch('/api/remote-sessions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(summary)
        });
        
        this.viewer.disconnect();
    }
}

// Usage
const session = new RemoteControlSession(window.remoteControlViewer);

// On page unload
window.addEventListener('beforeunload', () => {
    session.endSession();
});
```

---

## 🔄 State Transition Validation

### Valid Transitions

```javascript
// Only these transitions are allowed:
const validTransitions = {
    'disconnected': ['connecting', 'reconnecting'],
    'connecting': ['connected', 'error', 'disconnected'],
    'connected': ['disconnected', 'error'],
    'reconnecting': ['connecting', 'disconnected'],
    'error': ['reconnecting', 'disconnected']
};

// The ConnectionStateManager enforces these automatically
```

### Preventing Invalid States

```javascript
// The manager will NOT allow:
// - disconnected → error (go through reconnecting)
// - connected → reconnecting (disconnect first)
// - reconnecting → connected (must go through connecting)

// These invalid transitions are silently ignored by the state machine
```

---

## 📊 Data Flow Diagrams

### WebSocket Message Flow - Connected State

```
┌──────────────────────┐
│   CMS Viewer         │
│  (Browser)           │
└──────────────┬───────┘
               │
    User clicks on canvas
               │
               ▼
    ┌──────────────────────────┐
    │ getCanvasCoordinates()   │
    │ Normalize to 0.0-1.0     │
    └──────────────┬───────────┘
                   │
                   ▼
    ┌──────────────────────────┐
    │ canSendCommands() check  │
    │ (state === connected?)   │
    └──────────────┬───────────┘
                   │
                   ▼
    ┌──────────────────────────┐
    │ Create input_command     │
    │ {                        │
    │   type: 'input_command'  │
    │   command: {             │
    │     type: 'touch',       │
    │     x: 0.5,              │
    │     y: 0.5               │
    │   }                      │
    │ }                        │
    └──────────────┬───────────┘
                   │
                   ▼
    ┌──────────────────────────┐
    │ send() via WebSocket     │
    │ JSON.stringify()         │
    └──────────────┬───────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │ Relay Server         │
        │ Receives message     │
        └──────────┬───────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │ Routes to device     │
        │ via WebSocket        │
        └──────────┬───────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │ Android Device       │
        │ Receives & injects   │
        │ touch event          │
        └──────────────────────┘
```

---

## 🧪 Testing Examples

### Unit Test Example

```javascript
describe('ConnectionStateManager', () => {
    it('should transition from connecting to connected', () => {
        const manager = new ConnectionStateManager();
        
        // Start in connecting state
        manager.setState(ConnectionStateManager.States.CONNECTING);
        assert.equal(manager.getState(), ConnectionStateManager.States.CONNECTING);
        
        // Transition to connected
        manager.handleConnected();
        assert.equal(manager.getState(), ConnectionStateManager.States.CONNECTED);
        assert.equal(manager.reconnectAttempts, 0);
    });
    
    it('should schedule reconnection on error', (done) => {
        const manager = new ConnectionStateManager({
            reconnectDelayMs: 100
        });
        
        let reconnectAttempted = false;
        manager.onReconnectAttempt = () => {
            reconnectAttempted = true;
        };
        
        manager.handleError(ConnectionStateManager.ErrorTypes.NETWORK_ERROR, 'Test error');
        
        // Wait for scheduled reconnect
        setTimeout(() => {
            assert.equal(manager.getState(), ConnectionStateManager.States.RECONNECTING);
            done();
        }, 150);
    });
});
```

---

## 🚀 Deployment Checklist

- [ ] Load both JavaScript modules in production
- [ ] Configure WebSocket secure (WSS) URL
- [ ] Test all state transitions
- [ ] Verify error messages are appropriate
- [ ] Check button disabling/enabling works
- [ ] Test mobile responsiveness
- [ ] Configure max reconnect attempts
- [ ] Set up error logging/monitoring
- [ ] Document custom configurations
- [ ] Test with real device connections

---

**Version**: 2.0.0  
**Last Updated**: February 4, 2026
