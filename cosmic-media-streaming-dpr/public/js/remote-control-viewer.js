/**
 * Remote Control Viewer JavaScript
 * 
 * This script handles:
 * - WebSocket connection to relay server
 * - Receiving and displaying video frames
 * - Capturing and sending input events (touch, swipe, keyboard)
 * - Connection state management with auto-reconnect
 * - UI updates and statistics
 * 
 * File location: public/js/remote-control-viewer.js
 * 
 * @author Cosmic Development Team
 * @version 2.0.0
 */

class RemoteControlViewer {
    constructor(config) {
        this.config = config;
        this.ws = null;
        this.canvas = null;
        this.ctx = null;
        
        // Connection state manager
        this.connectionManager = new ConnectionStateManager({
            maxReconnectAttempts: config.maxReconnectAttempts || 5,
            reconnectDelayMs: config.reconnectDelayMs || 3000,
            autoReconnect: config.autoReconnect !== false,
            onStateChange: (state, error) => this.onConnectionStateChange(state, error),
            onReconnectCountdown: (seconds) => this.onReconnectCountdown(seconds),
            onReconnectAttempt: (attempt) => this.attemptConnection(),
            onMaxReconnectAttemptsReached: () => this.onMaxReconnectAttemptsReached()
        });
        
        // Frame timeout detection
        this.frameTimeoutHandle = null;
        this.lastFrameTime = Date.now();
        this.frameTimeoutDuration = 30000; // 30 seconds - show disconnected if no frame
        this.authSuccessTime = null;
        
        // Statistics
        this.stats = {
            framesReceived: 0,
            lastFrameTime: 0,
            fps: 0,
            latency: 0,
            sessionStartTime: Date.now()
        };
        
        // UI elements
        this.elements = {};
        
        // Mouse/touch state for swipe detection
        this.touchState = {
            startX: 0,
            startY: 0,
            endX: 0,
            endY: 0,
            isSwiping: false
        };
        
        this.init();
    }
    
    /**
     * Initialize viewer
     */
    init() {
        console.log('🎮 Initializing Remote Control Viewer v2.0');
        
        // Get DOM elements
        this.canvas = document.getElementById('device-screen');
        this.ctx = this.canvas.getContext('2d');
        this.elements = {
            connectionStatus: document.getElementById('connection-status'),
            loadingOverlay: document.getElementById('loading-overlay'),
            disconnectedOverlay: document.getElementById('disconnected-overlay'),
            reconnectingOverlay: document.getElementById('reconnecting-overlay'),
            errorMessage: document.getElementById('error-message'),
            reconnectCountdown: document.getElementById('reconnect-countdown'),
            reconnectAttempt: document.getElementById('reconnect-attempt'),
            statFps: document.getElementById('stat-fps'),
            statLatency: document.getElementById('stat-latency'),
            statDuration: document.getElementById('stat-duration'),
            btnBack: document.getElementById('btn-back'),
            btnHome: document.getElementById('btn-home'),
            btnKeyboard: document.getElementById('btn-keyboard'),
            btnRecord: document.getElementById('btn-record'),
            btnDisconnect: document.getElementById('btn-disconnect'),
            btnRetry: document.getElementById('btn-retry'),
            btnCancelReconnect: document.getElementById('btn-cancel-reconnect'),
            keyboardModal: document.getElementById('keyboard-modal'),
            btnCloseKeyboard: document.getElementById('btn-close-keyboard'),
            btnSendText: document.getElementById('btn-send-text'),
            btnCancelText: document.getElementById('btn-cancel-text'),
            keyboardInput: document.getElementById('keyboard-input')
        };
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Connect to WebSocket
        this.connect();
        
        // Start statistics update interval
        setInterval(() => this.updateStats(), 1000);
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Canvas events (touch/click)
        this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.canvas.addEventListener('mouseup', (e) => this.handleMouseUp(e));
        
        // Touch events (for mobile viewers)
        this.canvas.addEventListener('touchstart', (e) => this.handleTouchStart(e));
        this.canvas.addEventListener('touchmove', (e) => this.handleTouchMove(e));
        this.canvas.addEventListener('touchend', (e) => this.handleTouchEnd(e));
        
        // Control buttons
        this.elements.btnBack?.addEventListener('click', () => this.sendKeyPress(4)); // KEYCODE_BACK
        this.elements.btnHome?.addEventListener('click', () => this.sendKeyPress(3)); // KEYCODE_HOME
        this.elements.btnKeyboard?.addEventListener('click', () => this.showKeyboard());
        this.elements.btnDisconnect?.addEventListener('click', () => this.manualDisconnect());
        this.elements.btnRetry?.addEventListener('click', () => this.manualReconnect());
        this.elements.btnCancelReconnect?.addEventListener('click', () => this.cancelReconnect());
        
        // Keyboard modal
        this.elements.btnCloseKeyboard?.addEventListener('click', () => this.hideKeyboard());
        this.elements.btnCancelText?.addEventListener('click', () => this.hideKeyboard());
        this.elements.btnSendText?.addEventListener('click', () => this.sendText());
        
        // Recording (if enabled)
        if (this.config.canRecord && this.elements.btnRecord) {
            this.elements.btnRecord.addEventListener('click', () => this.toggleRecording());
        }
        
        // Prevent context menu on canvas
        this.canvas.addEventListener('contextmenu', (e) => e.preventDefault());
    }
    
    /**
     * Connect to WebSocket relay server
     */
    connect() {
        console.log('🔌 Connecting to relay server:', this.config.wsUrl);
        
        this.connectionManager.setState(ConnectionStateManager.States.CONNECTING);
        this.updateUIForState(ConnectionStateManager.States.CONNECTING);
        
        try {
            this.ws = new WebSocket(this.config.wsUrl);
            
            this.ws.onopen = () => this.onOpen();
            this.ws.onmessage = (event) => this.onMessage(event);
            this.ws.onerror = (error) => this.onError(error);
            this.ws.onclose = () => this.onClose();
            
        } catch (error) {
            console.error('❌ WebSocket connection error:', error);
            this.connectionManager.handleError(
                ConnectionStateManager.ErrorTypes.NETWORK_ERROR,
                error.message
            );
        }
    }
    
    /**
     * Attempt connection (called by connection manager)
     */
    attemptConnection() {
        this.connect();
    }
    
    /**
     * Manual reconnection triggered by user
     */
    manualReconnect() {
        console.log('🔄 Manual reconnection requested');
        this.connectionManager.manualReconnect();
        this.connect();
    }
    
    /**
     * Cancel auto-reconnection
     */
    cancelReconnect() {
        console.log('⏹️ Reconnection cancelled');
        this.connectionManager.manualDisconnect();
        this.updateUIForState(ConnectionStateManager.States.DISCONNECTED);
    }
    
    /**
     * Manual disconnect (user-initiated)
     */
    manualDisconnect() {
        console.log('🔌 User disconnection');
        this.connectionManager.manualDisconnect();
        this.disconnect();
    }
    
    /**
     * WebSocket opened
     */
    onOpen() {
        console.log('✅ WebSocket connected');
        
        // Send authentication message
        this.authenticate();
    }
    
    /**
     * Send authentication message
     */
    authenticate() {
        const authMessage = {
            type: 'auth',
            role: 'viewer',
            deviceId: this.config.deviceId,
            userId: this.config.userId,
            token: this.config.sessionToken // Use session token for viewer auth
        };
        
        this.send(authMessage);
        console.log('🔑 Authentication message sent');
    }
    
    /**
     * Connection state changed
     */
    onConnectionStateChange(state, error) {
        console.log(`📊 Connection state: ${state}`, error);
        this.updateUIForState(state, error);
        this.updateControlsState(state);
    }
    
    /**
     * Reconnect countdown update
     */
    onReconnectCountdown(seconds) {
        if (this.elements.reconnectCountdown) {
            this.elements.reconnectCountdown.textContent = seconds;
        }
    }
    
    /**
     * Max reconnect attempts reached
     */
    onMaxReconnectAttemptsReached() {
        console.error('❌ Maximum reconnection attempts reached');
        alert('Unable to reconnect to device. Please check the device status and try again.');
    }
    
    /**
     * Update UI based on connection state
     */
    updateUIForState(state, error = null) {
        // Hide all overlays first
        this.elements.loadingOverlay?.classList.add('hidden');
        this.elements.disconnectedOverlay?.classList.add('hidden');
        this.elements.reconnectingOverlay?.classList.add('hidden');
        
        switch (state) {
            case ConnectionStateManager.States.CONNECTING:
                this.elements.loadingOverlay?.classList.remove('hidden');
                this.updateConnectionStatus('Connecting...');
                break;
            
            case ConnectionStateManager.States.CONNECTED:
                this.updateConnectionStatus('Connected');
                // All overlays remain hidden
                break;
            
            case ConnectionStateManager.States.RECONNECTING:
                this.elements.reconnectingOverlay?.classList.remove('hidden');
                const status = this.connectionManager.getReconnectStatus();
                if (this.elements.reconnectAttempt) {
                    this.elements.reconnectAttempt.textContent = `Attempt ${status.attempt}/${status.maxAttempts}`;
                }
                this.updateConnectionStatus('Reconnecting...');
                break;
            
            case ConnectionStateManager.States.DISCONNECTED:
            case ConnectionStateManager.States.ERROR:
                this.elements.disconnectedOverlay?.classList.remove('hidden');
                if (this.elements.errorMessage) {
                    this.elements.errorMessage.textContent = this.connectionManager.getErrorMessage();
                }
                this.updateConnectionStatus(state === ConnectionStateManager.States.ERROR ? 'Error' : 'Disconnected');
                break;
        }
    }
    
    /**
     * Update control buttons state (enabled/disabled)
     */
    updateControlsState(state) {
        const isConnected = state === ConnectionStateManager.States.CONNECTED;
        const buttons = [
            this.elements.btnBack,
            this.elements.btnHome,
            this.elements.btnKeyboard,
            this.elements.btnRecord
        ];
        
        buttons.forEach(btn => {
            if (btn) {
                btn.disabled = !isConnected;
                btn.style.opacity = isConnected ? '1' : '0.5';
                btn.style.cursor = isConnected ? 'pointer' : 'not-allowed';
            }
        });
        
        // Update canvas pointer style
        this.canvas.style.cursor = isConnected ? 'crosshair' : 'not-allowed';
        this.canvas.style.opacity = isConnected ? '1' : '0.7';
    }
    
    /**
     * WebSocket message received
     */
    onMessage(event) {
        try {
            const message = JSON.parse(event.data);
            this.handleMessage(message);
        } catch (error) {
            console.error('❌ Error parsing message:', error);
        }
    }
    
    /**
     * Handle incoming message
     */
    handleMessage(message) {
        switch (message.type) {
            case 'auth_success':
                console.log('✅ Authentication successful');
                this.connectionManager.handleConnected();
                this.authSuccessTime = Date.now();
                // Start frame timeout detection
                this.startFrameTimeoutDetection();
                break;
                
            case 'auth_failed':
                console.error('❌ Authentication failed:', message.reason);
                this.connectionManager.handleError(
                    ConnectionStateManager.ErrorTypes.AUTH_FAILED,
                    message.reason || 'Authentication failed'
                );
                // Close connection on auth failure
                setTimeout(() => this.disconnect(), 2000);
                break;
                
            case 'frame':
                // Reset frame timeout when frame received
                this.lastFrameTime = Date.now();
                if (this.frameTimeoutHandle) {
                    clearTimeout(this.frameTimeoutHandle);
                }
                // Restart timeout detection
                this.startFrameTimeoutDetection();
                this.handleFrame(message);
                break;
                
            case 'device_disconnected':
                console.warn('⚠️ Device disconnected');
                this.connectionManager.handleError(
                    ConnectionStateManager.ErrorTypes.DEVICE_OFFLINE,
                    'Device disconnected from relay server'
                );
                break;
                
            case 'error':
                console.error('❌ Server error:', message.message);
                this.connectionManager.handleError(
                    ConnectionStateManager.ErrorTypes.UNKNOWN,
                    message.message || 'Server error'
                );
                break;
                
            default:
                console.warn('⚠️ Unknown message type:', message.type);
        }
    }
    
    /**
     * Handle video frame
     */
    handleFrame(message) {
        // Update frame statistics
        this.stats.framesReceived++;
        const now = Date.now();
        this.stats.latency = now - message.timestamp;
        
        // Calculate FPS
        if (this.stats.lastFrameTime > 0) {
            const deltaTime = (now - this.stats.lastFrameTime) / 1000;
            this.stats.fps = Math.round(1 / deltaTime);
        }
        this.stats.lastFrameTime = now;
        
        // Decode and display frame
        const img = new Image();
        img.onload = () => {
            this.ctx.drawImage(img, 0, 0, this.canvas.width, this.canvas.height);
        };
        img.src = 'data:image/jpeg;base64,' + message.data;
    }
    
    /**
     * Start frame timeout detection
     * If no frames arrive within 30 seconds after auth success, show disconnected overlay
     */
    startFrameTimeoutDetection() {
        // Clear existing timeout
        if (this.frameTimeoutHandle) {
            clearTimeout(this.frameTimeoutHandle);
        }
        
        // Set new timeout
        this.frameTimeoutHandle = setTimeout(() => {
            const timeSinceAuth = Date.now() - this.authSuccessTime;
            const timeSinceLastFrame = Date.now() - this.lastFrameTime;
            
            console.warn(`⚠️ Frame timeout detected! Auth success: ${timeSinceAuth}ms ago, Last frame: ${timeSinceLastFrame}ms ago`);
            
            // Only show disconnected if we're past auth success
            if (timeSinceAuth > 3000) { // Give it 3 seconds after auth to get first frame
                this.connectionManager.handleError(
                    ConnectionStateManager.ErrorTypes.TIMEOUT,
                    'No video frames received from device'
                );
            }
        }, this.frameTimeoutDuration);
    }
    
    /**
     * WebSocket error
     */
    onError(error) {
        console.error('❌ WebSocket error:', error);
        this.connectionManager.handleError(
            ConnectionStateManager.ErrorTypes.NETWORK_ERROR,
            'WebSocket connection error'
        );
    }
    
    /**
     * WebSocket closed
     */
    onClose() {
        console.log('🔌 WebSocket closed');
        // Clear frame timeout on close
        if (this.frameTimeoutHandle) {
            clearTimeout(this.frameTimeoutHandle);
        }
        
        // Only handle disconnection if not already in error/disconnected state
        if (this.connectionManager.getState() === ConnectionStateManager.States.CONNECTED ||
            this.connectionManager.getState() === ConnectionStateManager.States.CONNECTING) {
            this.connectionManager.handleDisconnected();
        }
    }
    
    /**
     * Disconnect from server
     */
    disconnect() {
        if (this.frameTimeoutHandle) {
            clearTimeout(this.frameTimeoutHandle);
        }
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
    }
    
    /**
     * Send message to server
     */
    send(message) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(message));
        } else {
            console.warn('⚠️ WebSocket not connected, cannot send message');
        }
    }
    
    /**
     * Handle mouse down (start of touch/swipe)
     */
    handleMouseDown(e) {
        if (!this.connectionManager.canSendCommands()) {
            return; // Ignore if not connected
        }
        
        const coords = this.getCanvasCoordinates(e);
        this.touchState.startX = coords.x;
        this.touchState.startY = coords.y;
        this.touchState.isSwiping = true;
    }
    
    /**
     * Handle mouse move (detecting swipe)
     */
    handleMouseMove(e) {
        if (!this.connectionManager.canSendCommands() || !this.touchState.isSwiping) {
            return;
        }
        
        const coords = this.getCanvasCoordinates(e);
        this.touchState.endX = coords.x;
        this.touchState.endY = coords.y;
    }
    
    /**
     * Handle mouse up (end of touch/swipe)
     */
    handleMouseUp(e) {
        if (!this.connectionManager.canSendCommands() || !this.touchState.isSwiping) {
            return;
        }
        
        const coords = this.getCanvasCoordinates(e);
        this.touchState.endX = coords.x;
        this.touchState.endY = coords.y;
        this.touchState.isSwiping = false;
        
        // Determine if it's a tap or swipe
        const deltaX = Math.abs(this.touchState.endX - this.touchState.startX);
        const deltaY = Math.abs(this.touchState.endY - this.touchState.startY);
        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        
        if (distance < 10) {
            // It's a tap
            this.sendTouch(coords.x, coords.y);
        } else {
            // It's a swipe
            this.sendSwipe(
                this.touchState.startX,
                this.touchState.startY,
                this.touchState.endX,
                this.touchState.endY
            );
        }
    }
    
    /**
     * Handle touch start (mobile)
     */
    handleTouchStart(e) {
        e.preventDefault();
        const touch = e.touches[0];
        this.handleMouseDown(touch);
    }
    
    /**
     * Handle touch move (mobile)
     */
    handleTouchMove(e) {
        e.preventDefault();
        const touch = e.touches[0];
        this.handleMouseMove(touch);
    }
    
    /**
     * Handle touch end (mobile)
     */
    handleTouchEnd(e) {
        e.preventDefault();
        const touch = e.changedTouches[0];
        this.handleMouseUp(touch);
    }
    
    /**
     * Get normalized coordinates from canvas click
     */
    getCanvasCoordinates(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;
        return { x, y };
    }
    
    /**
     * Send touch event to device
     */
    sendTouch(x, y) {
        if (!this.config.canControl || !this.connectionManager.canSendCommands()) {
            if (!this.config.canControl) {
                alert('You do not have permission to control this device');
            }
            return;
        }
        
        const command = {
            type: 'input_command',
            command: {
                type: 'touch',
                x: x,
                y: y,
                normalized: true
            }
        };
        
        this.send(command);
        console.log('👆 Touch sent:', x, y);
    }
    
    /**
     * Send swipe gesture to device
     */
    sendSwipe(startX, startY, endX, endY) {
        if (!this.config.canControl || !this.connectionManager.canSendCommands()) {
            return;
        }
        
        const command = {
            type: 'input_command',
            command: {
                type: 'swipe',
                x: startX,
                y: startY,
                endX: endX,
                endY: endY,
                normalized: true,
                duration: 300
            }
        };
        
        this.send(command);
        console.log('👉 Swipe sent:', startX, startY, '->', endX, endY);
    }
    
    /**
     * Send key press to device
     */
    sendKeyPress(keyCode) {
        if (!this.config.canControl || !this.connectionManager.canSendCommands()) {
            return;
        }
        
        const command = {
            type: 'input_command',
            command: {
                type: 'key',
                keyCode: keyCode
            }
        };
        
        this.send(command);
        console.log('⌨️ Key press sent:', keyCode);
    }
    
    /**
     * Show keyboard modal
     */
    showKeyboard() {
        if (!this.connectionManager.canSendCommands()) {
            return;
        }
        this.elements.keyboardModal.classList.remove('hidden');
        this.elements.keyboardInput.focus();
    }
    
    /**
     * Hide keyboard modal
     */
    hideKeyboard() {
        this.elements.keyboardModal.classList.add('hidden');
        this.elements.keyboardInput.value = '';
    }
    
    /**
     * Send text input to device
     */
    sendText() {
        const text = this.elements.keyboardInput.value;
        if (!text) return;
        
        if (!this.config.canControl || !this.connectionManager.canSendCommands()) {
            if (!this.config.canControl) {
                alert('You do not have permission to control this device');
            }
            return;
        }
        
        const command = {
            type: 'input_command',
            command: {
                type: 'text',
                text: text
            }
        };
        
        this.send(command);
        console.log('💬 Text sent:', text);
        
        this.hideKeyboard();
    }
    
    /**
     * Toggle recording
     */
    toggleRecording() {
        // TODO: Implement recording functionality
        console.log('🎥 Recording not yet implemented');
        alert('Recording feature coming soon!');
    }
    
    /**
     * Update connection status display
     */
    updateConnectionStatus(status) {
        this.elements.connectionStatus.textContent = status;
        
        const color = {
            'Connected': 'text-green-500',
            'Connecting...': 'text-yellow-500',
            'Reconnecting...': 'text-orange-500',
            'Disconnected': 'text-red-500',
            'Error': 'text-red-500',
            'Connection Error': 'text-red-500',
            'Authentication Failed': 'text-red-500'
        }[status] || 'text-gray-500';
        
        this.elements.connectionStatus.className = color + ' font-semibold';
    }
    
    /**
     * Update statistics display
     */
    updateStats() {
        // FPS
        this.elements.statFps.textContent = this.stats.fps;
        
        // Latency
        this.elements.statLatency.textContent = this.stats.latency + ' ms';
        
        // Session duration
        const duration = Math.floor((Date.now() - this.stats.sessionStartTime) / 1000);
        const hours = Math.floor(duration / 3600);
        const minutes = Math.floor((duration % 3600) / 60);
        const seconds = duration % 60;
        this.elements.statDuration.textContent = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    /**
     * Cleanup on viewer destruction
     */
    destroy() {
        this.disconnect();
        this.connectionManager.destroy();
        if (this.frameTimeoutHandle) {
            clearTimeout(this.frameTimeoutHandle);
        }
    }
}

// Initialize viewer when page loads
document.addEventListener('DOMContentLoaded', () => {
    if (window.remoteControlConfig) {
        const viewer = new RemoteControlViewer(window.remoteControlConfig);
        window.remoteControlViewer = viewer; // For debugging
    } else {
        console.error('❌ Remote control configuration not found');
    }
});
