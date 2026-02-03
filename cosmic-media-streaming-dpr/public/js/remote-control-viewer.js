/**
 * Remote Control Viewer JavaScript
 * 
 * This script handles:
 * - WebSocket connection to relay server
 * - Receiving and displaying video frames
 * - Capturing and sending input events (touch, swipe, keyboard)
 * - UI updates and statistics
 * 
 * File location: public/js/remote-control-viewer.js
 * 
 * @author Cosmic Development Team
 * @version 1.0.0 (POC)
 */

class RemoteControlViewer {
    constructor(config) {
        this.config = config;
        this.ws = null;
        this.isConnected = false;
        this.canvas = null;
        this.ctx = null;
        
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
        console.log('🎮 Initializing Remote Control Viewer');
        
        // Get DOM elements
        this.canvas = document.getElementById('device-screen');
        this.ctx = this.canvas.getContext('2d');
        this.elements = {
            connectionStatus: document.getElementById('connection-status'),
            loadingOverlay: document.getElementById('loading-overlay'),
            disconnectedOverlay: document.getElementById('disconnected-overlay'),
            statFps: document.getElementById('stat-fps'),
            statLatency: document.getElementById('stat-latency'),
            statDuration: document.getElementById('stat-duration'),
            btnBack: document.getElementById('btn-back'),
            btnHome: document.getElementById('btn-home'),
            btnKeyboard: document.getElementById('btn-keyboard'),
            btnRecord: document.getElementById('btn-record'),
            btnDisconnect: document.getElementById('btn-disconnect'),
            btnRetry: document.getElementById('btn-retry'),
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
        this.elements.btnDisconnect?.addEventListener('click', () => this.disconnect());
        this.elements.btnRetry?.addEventListener('click', () => this.connect());
        
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
        
        this.elements.loadingOverlay.classList.remove('hidden');
        this.elements.disconnectedOverlay.classList.add('hidden');
        this.updateConnectionStatus('Connecting...');
        
        try {
            this.ws = new WebSocket(this.config.wsUrl);
            
            this.ws.onopen = () => this.onOpen();
            this.ws.onmessage = (event) => this.onMessage(event);
            this.ws.onerror = (error) => this.onError(error);
            this.ws.onclose = () => this.onClose();
            
        } catch (error) {
            console.error('❌ WebSocket connection error:', error);
            this.onError(error);
        }
    }
    
    /**
     * WebSocket opened
     */
    onOpen() {
        console.log('✅ WebSocket connected');
        this.isConnected = true;
        
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
                this.updateConnectionStatus('Connected');
                this.elements.loadingOverlay.classList.add('hidden');
                break;
                
            case 'auth_failed':
                console.error('❌ Authentication failed:', message.reason);
                this.updateConnectionStatus('Authentication Failed');
                alert('Authentication failed: ' + message.reason);
                this.disconnect();
                break;
                
            case 'frame':
                this.handleFrame(message);
                break;
                
            case 'device_disconnected':
                console.warn('⚠️ Device disconnected');
                this.showDisconnectedOverlay();
                break;
                
            case 'error':
                console.error('❌ Server error:', message.message);
                alert('Error: ' + message.message);
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
     * WebSocket error
     */
    onError(error) {
        console.error('❌ WebSocket error:', error);
        this.updateConnectionStatus('Connection Error');
    }
    
    /**
     * WebSocket closed
     */
    onClose() {
        console.log('🔌 WebSocket closed');
        this.isConnected = false;
        this.updateConnectionStatus('Disconnected');
        this.showDisconnectedOverlay();
    }
    
    /**
     * Disconnect from server
     */
    disconnect() {
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
            console.warn('⚠️ WebSocket not connected');
        }
    }
    
    /**
     * Handle mouse down (start of touch/swipe)
     */
    handleMouseDown(e) {
        const coords = this.getCanvasCoordinates(e);
        this.touchState.startX = coords.x;
        this.touchState.startY = coords.y;
        this.touchState.isSwiping = true;
    }
    
    /**
     * Handle mouse move (detecting swipe)
     */
    handleMouseMove(e) {
        if (this.touchState.isSwiping) {
            const coords = this.getCanvasCoordinates(e);
            this.touchState.endX = coords.x;
            this.touchState.endY = coords.y;
        }
    }
    
    /**
     * Handle mouse up (end of touch/swipe)
     */
    handleMouseUp(e) {
        if (!this.touchState.isSwiping) return;
        
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
        if (!this.config.canControl) {
            alert('You do not have permission to control this device');
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
        if (!this.config.canControl) return;
        
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
        if (!this.config.canControl) return;
        
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
        
        if (!this.config.canControl) {
            alert('You do not have permission to control this device');
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
            'Disconnected': 'text-red-500',
            'Connection Error': 'text-red-500',
            'Authentication Failed': 'text-red-500'
        }[status] || 'text-gray-500';
        
        this.elements.connectionStatus.className = color + ' font-semibold';
    }
    
    /**
     * Show disconnected overlay
     */
    showDisconnectedOverlay() {
        this.elements.loadingOverlay.classList.add('hidden');
        this.elements.disconnectedOverlay.classList.remove('hidden');
        this.elements.disconnectedOverlay.style.display = 'flex';
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
