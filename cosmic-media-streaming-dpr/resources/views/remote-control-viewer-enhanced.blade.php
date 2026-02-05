<!DOCTYPE html>
<html lang="en" class="dark">
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
            background: #0f0f0f;
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
            background: #1e1e1e;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .device-info h1 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #fff;
        }
        
        .device-info p {
            font-size: 12px;
            color: #888;
        }
        
        .connection-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            background: #2a2a2a;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .status-dot.connected { background: #34c759; }
        .status-dot.connecting { background: #ff9f0a; }
        .status-dot.reconnecting { background: #ff9f0a; }
        .status-dot.disconnected { background: #ff3b30; }
        .status-dot.error { background: #ff3b30; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .stats {
            display: flex;
            gap: 16px;
            padding-right: 16px;
            border-right: 1px solid #333;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            font-variant-numeric: tabular-nums;
        }
        
        .controls-bar {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: #2a2a2a;
            color: #fff;
        }
        
        .btn-icon:hover:not(:disabled) {
            background: #3a3a3a;
        }
        
        .btn-primary {
            background: #0a84ff;
            color: #fff;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #0066cc;
        }
        
        .btn-danger {
            background: #ff453a;
            color: #fff;
        }
        
        .btn-danger:hover:not(:disabled) {
            background: #d93731;
        }
        
        /* Main Content */
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            background: #0f0f0f;
            position: relative;
            overflow: hidden;
        }
        
        .screen-container {
            position: relative;
            max-width: 100%;
            max-height: 100%;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        }
        
        #device-screen {
            display: block;
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: calc(100vh - 120px);
            object-fit: contain;
            cursor: crosshair;
            transition: opacity 0.3s;
        }
        
        /* Overlays */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        
        .overlay.hidden {
            display: none !important;
        }
        
        /* Loading Overlay */
        #loading-overlay {
            background: rgba(0,0,0,0.95);
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #333;
            border-top-color: #0a84ff;
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
        
        /* Disconnected Overlay */
        #disconnected-overlay {
            background: rgba(255, 59, 48, 0.05);
            backdrop-filter: blur(20px);
        }
        
        .overlay-content {
            text-align: center;
            max-width: 400px;
            padding: 32px;
            background: rgba(30, 30, 30, 0.95);
            border-radius: 16px;
            border: 1px solid #333;
        }
        
        .overlay-icon {
            font-size: 64px;
            margin-bottom: 16px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .overlay-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #fff;
        }
        
        .overlay-message {
            font-size: 14px;
            color: #aaa;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        
        #error-message {
            color: #ff9f0a;
            font-weight: 500;
        }
        
        .overlay-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        /* Reconnecting Overlay */
        #reconnecting-overlay {
            background: rgba(255, 159, 10, 0.05);
            backdrop-filter: blur(20px);
        }
        
        .reconnect-info {
            font-size: 16px;
            color: #ff9f0a;
            margin-top: 16px;
            font-weight: 500;
        }
        
        #reconnect-countdown {
            font-size: 32px;
            font-weight: 700;
            color: #0a84ff;
            margin: 0 8px;
            font-variant-numeric: tabular-nums;
        }
        
        #reconnect-attempt {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }
        
        /* Keyboard Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal.hidden {
            display: none;
        }
        
        .modal-content {
            background: #1e1e1e;
            padding: 24px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            border: 1px solid #333;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-input {
            width: 100%;
            padding: 12px;
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
        }
        
        .modal-input:focus {
            outline: none;
            border-color: #0a84ff;
        }
        
        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 12px;
                padding: 12px 16px;
            }
            
            .stats {
                border-right: none;
                padding-right: 0;
            }
            
            .stat-label {
                font-size: 9px;
            }
            
            .stat-value {
                font-size: 12px;
            }
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
                <div class="connection-status">
                    <div class="status-dot connecting"></div>
                    <span id="connection-status">Connecting...</span>
                </div>
            </div>
            
            <div class="header-right">
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-label">FPS</span>
                        <span class="stat-value" id="stat-fps">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Latency</span>
                        <span class="stat-value" id="stat-latency">-- ms</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Duration</span>
                        <span class="stat-value" id="stat-duration">00:00:00</span>
                    </div>
                </div>
                
                <div class="controls-bar">
                    <button id="btn-back" class="btn btn-icon" title="Back" disabled>
                        ⬅️
                    </button>
                    <button id="btn-home" class="btn btn-icon" title="Home" disabled>
                        🏠
                    </button>
                    <button id="btn-keyboard" class="btn btn-icon" title="Keyboard" disabled>
                        ⌨️
                    </button>
                    <button id="btn-disconnect" class="btn btn-danger">
                        Disconnect
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Screen View -->
        <div class="content">
            <div class="screen-container">
                <canvas id="device-screen" width="1080" height="1920"></canvas>
                
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="overlay">
                    <div class="spinner"></div>
                    <p class="loading-text">Connecting to device...</p>
                </div>
                
                <!-- Disconnected Overlay -->
                <div id="disconnected-overlay" class="overlay hidden">
                    <div class="overlay-content">
                        <div class="overlay-icon">⚠️</div>
                        <h2 class="overlay-title">Device Disconnected</h2>
                        <p class="overlay-message">
                            The device is not currently connected.
                            <br><br>
                            <span id="error-message">Connection lost</span>
                        </p>
                        <div class="overlay-actions">
                            <button id="btn-retry" class="btn btn-primary">
                                🔄 Retry Connection
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Reconnecting Overlay -->
                <div id="reconnecting-overlay" class="overlay hidden">
                    <div class="overlay-content">
                        <div class="spinner"></div>
                        <h2 class="overlay-title">Reconnecting...</h2>
                        <p class="overlay-message">
                            Attempting to reconnect to the device
                        </p>
                        <div class="reconnect-info">
                            Retrying in <span id="reconnect-countdown">5</span> seconds
                        </div>
                        <p id="reconnect-attempt" class="overlay-message">Attempt 1/5</p>
                        <div class="overlay-actions">
                            <button id="btn-cancel-reconnect" class="btn btn-danger">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Keyboard Modal -->
        <div id="keyboard-modal" class="modal hidden">
            <div class="modal-content">
                <h3 class="modal-title">Send Text Input</h3>
                <div class="modal-body">
                    <input type="text" id="keyboard-input" class="modal-input" 
                           placeholder="Type text to send to device..." 
                           maxlength="500">
                </div>
                <div class="modal-footer">
                    <button id="btn-cancel-text" class="btn" style="background: #2a2a2a;">
                        Cancel
                    </button>
                    <button id="btn-send-text" class="btn btn-primary">
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Load JavaScript modules with cache busting --}}
    <script src="{{ asset('js/connection-state-manager.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/remote-control-viewer.js') }}?v={{ time() }}"></script>
    
    <script>
        // Configuration from backend
        window.remoteControlConfig = {
            wsUrl: @json($wsUrl),
            deviceId: @json($deviceId),
            deviceToken: @json($deviceToken),
            deviceName: @json($deviceName),
            userId: @json($userId),
            userName: @json($userName),
            sessionToken: @json($sessionToken ?? ''),
            canControl: @json($canControl ?? true),
            canRecord: @json($canRecord ?? false),
            maxReconnectAttempts: 5,
            reconnectDelayMs: 3000,
            autoReconnect: true
        };
    </script>
</body>
</html>
