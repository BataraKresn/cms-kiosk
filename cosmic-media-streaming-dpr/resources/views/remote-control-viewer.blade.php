<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Remote Control - {{ $deviceName }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1a1a1a;
            color: #fff;
            overflow: hidden;
        }
        
        #app {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        /* Header */
        .header {
            background: #2d2d2d;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #404040;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .device-info h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .device-info p {
            font-size: 13px;
            color: #aaa;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-connecting {
            background: #ff9500;
            color: #fff;
        }
        
        .status-connected {
            background: #34c759;
            color: #fff;
        }
        
        .status-error {
            background: #ff3b30;
            color: #fff;
        }
        
        .status-disconnected {
            background: #636366;
            color: #fff;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #aaa;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #007aff;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #0051d5;
        }
        
        .btn-danger {
            background: #ff3b30;
            color: #fff;
        }
        
        .btn-danger:hover {
            background: #d70015;
        }
        
        /* Main Content */
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            background: #1a1a1a;
            position: relative;
        }
        
        .screen-container {
            position: relative;
            max-width: 100%;
            max-height: 100%;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        
        #screenCanvas {
            display: block;
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: calc(100vh - 150px);
            object-fit: contain;
            cursor: crosshair;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #333;
            border-top-color: #007aff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            margin-top: 20px;
            font-size: 14px;
            color: #aaa;
        }
        
        .error-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
        
        .error-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .error-message {
            font-size: 16px;
            color: #ff3b30;
            text-align: center;
            max-width: 400px;
        }
        
        /* Controls Panel */
        .controls-panel {
            position: fixed;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            background: #2d2d2d;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 100;
        }
        
        .control-btn {
            width: 50px;
            height: 50px;
            border: none;
            background: #3d3d3d;
            color: #fff;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            margin-bottom: 10px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .control-btn:hover {
            background: #4d4d4d;
            transform: scale(1.05);
        }
        
        .control-btn:active {
            transform: scale(0.95);
        }
        
        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2d2d2d;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            font-size: 14px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="device-info">
                    <h1>{{ $deviceName }}</h1>
                    <p>Device ID: {{ $deviceId }}</p>
                </div>
                <span id="statusBadge" class="status-badge status-connecting">Connecting...</span>
            </div>
            
            <div class="header-right">
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-label">FPS</span>
                        <span class="stat-value" id="fpsValue">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Latency</span>
                        <span class="stat-value" id="latencyValue">-- ms</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Frames</span>
                        <span class="stat-value" id="framesValue">0</span>
                    </div>
                </div>
                
                <button id="reconnectBtn" class="btn btn-primary hidden">Reconnect</button>
                <button id="disconnectBtn" class="btn btn-danger">Disconnect</button>
            </div>
        </div>
        
        <!-- Main Screen View -->
        <div class="content">
            <div class="screen-container">
                <canvas id="screenCanvas" width="1080" height="1920"></canvas>
                
                <!-- Loading Overlay -->
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="spinner"></div>
                    <p class="loading-text">Connecting to device...</p>
                </div>
                
                <!-- Error Overlay -->
                <div id="errorOverlay" class="error-overlay hidden">
                    <div class="error-icon">⚠️</div>
                    <p class="error-message" id="errorMessage">Connection failed</p>
                </div>
            </div>
        </div>
        
        <!-- Control Panel -->
        <div class="controls-panel">
            <button class="control-btn" title="Home" onclick="sendKey('home')">🏠</button>
            <button class="control-btn" title="Back" onclick="sendKey('back')">⬅️</button>
            <button class="control-btn" title="Recent Apps" onclick="sendKey('recent')">📱</button>
            <button class="control-btn" title="Volume Up" onclick="sendKey('volume_up')">🔊</button>
            <button class="control-btn" title="Volume Down" onclick="sendKey('volume_down')">🔉</button>
        </div>
    </div>

    <script>
        // Configuration from backend
        const config = {
            wsUrl: @json($wsUrl),
            deviceId: @json($deviceId),
            deviceToken: @json($deviceToken),
            deviceName: @json($deviceName),
            userId: @json($userId),
            userName: @json($userName),
        };

        // WebSocket connection
        let ws = null;
        let isConnected = false;
        let reconnectAttempts = 0;
        const MAX_RECONNECT_ATTEMPTS = 5;
        const RECONNECT_DELAY = 3000;

        // Statistics
        let frameCount = 0;
        let lastFrameTime = Date.now();
        let fps = 0;
        let lastPingTime = 0;
        let latency = 0;

        // DOM elements
        const canvas = document.getElementById('screenCanvas');
        const ctx = canvas.getContext('2d');
        const statusBadge = document.getElementById('statusBadge');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const errorOverlay = document.getElementById('errorOverlay');
        const errorMessage = document.getElementById('errorMessage');
        const reconnectBtn = document.getElementById('reconnectBtn');
        const disconnectBtn = document.getElementById('disconnectBtn');
        const fpsValue = document.getElementById('fpsValue');
        const latencyValue = document.getElementById('latencyValue');
        const framesValue = document.getElementById('framesValue');

        /**
         * Connect to WebSocket relay server
         */
        function connect() {
            console.log('🔌 Connecting to relay server:', config.wsUrl);
            updateStatus('connecting', 'Connecting...');
            
            ws = new WebSocket(config.wsUrl);
            
            ws.onopen = () => {
                console.log('✅ WebSocket connected');
                isConnected = true;
                reconnectAttempts = 0;
                
                // Send authentication as viewer
                const authMsg = {
                    type: 'auth',
                    role: 'viewer',
                    device_id: config.deviceId,
                    user_id: config.userId,
                    user_name: config.userName
                };
                
                ws.send(JSON.stringify(authMsg));
                console.log('📤 Auth sent:', authMsg);
                
                updateStatus('connected', 'Connected');
                loadingOverlay.classList.add('hidden');
                errorOverlay.classList.add('hidden');
                reconnectBtn.classList.add('hidden');
            };
            
            ws.onmessage = (event) => {
                try {
                    // Check if binary frame (image)
                    if (event.data instanceof Blob) {
                        handleFrameBlob(event.data);
                        return;
                    }
                    
                    // Handle JSON messages
                    const message = JSON.parse(event.data);
                    handleMessage(message);
                    
                } catch (error) {
                    console.error('Error handling message:', error);
                }
            };
            
            ws.onerror = (error) => {
                console.error('❌ WebSocket error:', error);
                updateStatus('error', 'Connection Error');
            };
            
            ws.onclose = (event) => {
                console.log('🔌 WebSocket closed:', event.code, event.reason);
                isConnected = false;
                updateStatus('disconnected', 'Disconnected');
                loadingOverlay.classList.remove('hidden');
                
                // Auto-reconnect
                if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                    reconnectAttempts++;
                    console.log(`🔄 Reconnecting in ${RECONNECT_DELAY/1000}s (attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
                    setTimeout(connect, RECONNECT_DELAY);
                } else {
                    showError('Connection lost. Click Reconnect to try again.');
                    reconnectBtn.classList.remove('hidden');
                }
            };
        }

        /**
         * Handle incoming messages
         */
        function handleMessage(message) {
            switch (message.type) {
                case 'auth_success':
                    console.log('✅ Authentication successful');
                    showToast('Connected to ' + config.deviceName);
                    break;
                
                case 'auth_error':
                    console.error('❌ Authentication failed:', message.error);
                    showError('Authentication failed: ' + message.error);
                    break;
                
                case 'frame':
                    // Base64 encoded frame
                    handleFrameBase64(message.data);
                    break;
                
                case 'pong':
                    // Heartbeat response
                    if (lastPingTime > 0) {
                        latency = Date.now() - lastPingTime;
                        latencyValue.textContent = latency + ' ms';
                    }
                    break;
                
                case 'device_disconnected':
                    console.warn('⚠️ Device disconnected');
                    showError('Device has disconnected');
                    break;
                
                default:
                    console.log('Received message:', message);
            }
        }

        /**
         * Handle frame as Blob (binary)
         */
        function handleFrameBlob(blob) {
            const img = new Image();
            const url = URL.createObjectURL(blob);
            
            img.onload = () => {
                // Update canvas size if needed
                if (canvas.width !== img.width || canvas.height !== img.height) {
                    canvas.width = img.width;
                    canvas.height = img.height;
                }
                
                // Draw frame
                ctx.drawImage(img, 0, 0);
                URL.revokeObjectURL(url);
                
                // Update stats
                updateFrameStats();
            };
            
            img.onerror = () => {
                console.error('Failed to load frame');
                URL.revokeObjectURL(url);
            };
            
            img.src = url;
        }

        /**
         * Handle frame as Base64
         */
        function handleFrameBase64(base64Data) {
            const img = new Image();
            
            img.onload = () => {
                // Update canvas size if needed
                if (canvas.width !== img.width || canvas.height !== img.height) {
                    canvas.width = img.width;
                    canvas.height = img.height;
                }
                
                // Draw frame
                ctx.drawImage(img, 0, 0);
                
                // Update stats
                updateFrameStats();
            };
            
            img.onerror = () => {
                console.error('Failed to load frame');
            };
            
            img.src = 'data:image/jpeg;base64,' + base64Data;
        }

        /**
         * Update frame statistics
         */
        function updateFrameStats() {
            frameCount++;
            framesValue.textContent = frameCount;
            
            // Calculate FPS every second
            const now = Date.now();
            const elapsed = now - lastFrameTime;
            if (elapsed >= 1000) {
                fps = Math.round((frameCount * 1000) / elapsed);
                fpsValue.textContent = fps;
                frameCount = 0;
                lastFrameTime = now;
            }
        }

        /**
         * Send touch event to device
         */
        canvas.addEventListener('click', (event) => {
            if (!isConnected) return;
            
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            
            const x = (event.clientX - rect.left) * scaleX;
            const y = (event.clientY - rect.top) * scaleY;
            
            // Normalize coordinates (0.0 - 1.0)
            const normalizedX = x / canvas.width;
            const normalizedY = y / canvas.height;
            
            const touchMsg = {
                type: 'input',
                action: 'touch',
                x: normalizedX,
                y: normalizedY
            };
            
            ws.send(JSON.stringify(touchMsg));
            console.log('📤 Touch sent:', touchMsg);
            
            // Visual feedback
            drawTouchIndicator(x, y);
        });

        /**
         * Send key event to device
         */
        function sendKey(key) {
            if (!isConnected) return;
            
            const keyMsg = {
                type: 'input',
                action: 'key',
                key: key
            };
            
            ws.send(JSON.stringify(keyMsg));
            console.log('📤 Key sent:', keyMsg);
        }

        /**
         * Draw touch indicator on canvas
         */
        function drawTouchIndicator(x, y) {
            ctx.save();
            ctx.strokeStyle = '#007aff';
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.arc(x, y, 20, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();
            
            // Fade out after 200ms
            setTimeout(() => {
                // Canvas will be redrawn with next frame
            }, 200);
        }

        /**
         * Update connection status
         */
        function updateStatus(status, text) {
            statusBadge.className = 'status-badge status-' + status;
            statusBadge.textContent = text;
        }

        /**
         * Show error overlay
         */
        function showError(message) {
            errorMessage.textContent = message;
            errorOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('hidden');
        }

        /**
         * Show toast notification
         */
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        /**
         * Disconnect from relay server
         */
        function disconnect() {
            if (ws) {
                ws.close();
                ws = null;
            }
            
            // Redirect back to remotes list
            window.location.href = '/back-office/resources/remotes';
        }

        /**
         * Send heartbeat ping
         */
        function sendHeartbeat() {
            if (isConnected && ws) {
                lastPingTime = Date.now();
                ws.send(JSON.stringify({ type: 'ping' }));
            }
        }

        // Event listeners
        reconnectBtn.addEventListener('click', () => {
            reconnectAttempts = 0;
            connect();
        });

        disconnectBtn.addEventListener('click', disconnect);

        // Start heartbeat interval
        setInterval(sendHeartbeat, 15000);

        // Connect on page load
        connect();

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (ws) {
                ws.close();
            }
        });
    </script>
</body>
</html>
