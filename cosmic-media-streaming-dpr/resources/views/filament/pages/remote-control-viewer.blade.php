<!-- 
  Remote Control Viewer Page (Filament Blade Template)
  
  This page displays the Android device screen and allows interaction.
  
  File location: 
  resources/views/filament/pages/remote-control-viewer.blade.php
-->

<x-filament-panels::page>
    <div class="remote-control-container">
        
        {{-- Header with device info and controls --}}
        <div class="viewer-header bg-white/90 dark:bg-gray-800/80 rounded-2xl shadow-xl p-5 mb-5 backdrop-blur">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                {{-- Device Info --}}
                <div class="flex items-center gap-4">
                    <div class="status-dot {{ $this->record->status === 'Connected' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></div>
                    <div>
                        <h2 class="text-xl font-semibold tracking-tight">{{ $this->record->name }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                            <span class="chip">IP: {{ $this->record->ip_address }}</span>
                            <span class="chip">Port: {{ $this->record->remote_control_port }}</span>
                            <span class="chip">Status: <span id="connection-status" class="font-medium">Connecting...</span></span>
                        </div>
                    </div>
                </div>
                
                {{-- Control Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        id="btn-back"
                        class="control-btn"
                        title="Back Button">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back</span>
                    </button>
                    
                    <button 
                        type="button"
                        id="btn-home"
                        class="control-btn"
                        title="Home Button">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Home</span>
                    </button>
                    
                    <button 
                        type="button"
                        id="btn-keyboard"
                        class="control-btn"
                        title="Show Keyboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <span>Keyboard</span>
                    </button>
                    
                    @if($canRecord)
                    <button 
                        type="button"
                        id="btn-record"
                        class="control-btn control-btn-danger"
                        title="Start Recording">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="5"/>
                        </svg>
                        <span id="record-label">Record</span>
                    </button>
                    @endif
                    
                    <button 
                        type="button"
                        id="btn-disconnect"
                        class="control-btn control-btn-outline"
                        title="Disconnect">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Disconnect</span>
                    </button>
                </div>
            </div>
            
            {{-- Stats Bar --}}
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600 md:grid-cols-4">
                <div class="stat-card">
                    <span class="stat-label">FPS</span>
                    <span id="stat-fps" class="stat-value">0</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Latency</span>
                    <span id="stat-latency" class="stat-value">- ms</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Resolution</span>
                    <span id="stat-resolution" class="stat-value">{{ $this->record->screen_resolution ?? 'Unknown' }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Session</span>
                    <span id="stat-duration" class="stat-value">00:00:00</span>
                </div>
            </div>
        </div>
        
        {{-- Screen Viewer --}}
        <div class="viewer-shell bg-gray-900 rounded-2xl shadow-2xl overflow-hidden relative">
            <div class="screen-stage flex justify-center items-center">
                {{-- Canvas for device screen --}}
                <canvas 
                    id="device-screen"
                    class="device-canvas cursor-pointer"
                    width="1080"
                    height="1920"
                    style="max-width: 100%; height: auto;">
                </canvas>
                
                {{-- Loading Overlay --}}
                <div id="loading-overlay" class="absolute inset-0 flex items-center justify-center bg-gray-900/90">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-white mx-auto mb-4"></div>
                        <p class="text-white text-lg">Connecting to device...</p>
                        <p class="text-gray-400 text-sm mt-2">Please wait</p>
                    </div>
                </div>
                
                {{-- Disconnected Overlay --}}
                <div id="disconnected-overlay" class="absolute inset-0 hidden items-center justify-center bg-gray-900/90">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-white text-lg font-semibold">Device Disconnected</p>
                        <p class="text-gray-400 text-sm mt-2">The device is not currently connected</p>
                        <button 
                            type="button"
                            id="btn-retry"
                            class="mt-4 px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                            Retry Connection
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Toast Notification Container --}}
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 400px;"></div>

        {{-- Keyboard Modal (Hidden by default) --}}
        <div id="keyboard-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-96">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Send Text</h3>
                    <button type="button" id="btn-close-keyboard" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <textarea 
                    id="keyboard-input"
                    rows="4"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Type text to send to device..."></textarea>
                <div class="mt-4 flex justify-end space-x-2">
                    <button 
                        type="button"
                        id="btn-cancel-text"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                        Cancel
                    </button>
                    <button 
                        type="button"
                        id="btn-send-text"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                        Send Text
                    </button>
                </div>
            </div>
        </div>
        
    </div>
    
    @push('styles')
    <style>
        .remote-control-container {
            padding-bottom: 24px;
        }

        .viewer-header {
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 9999px;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            background: rgba(148, 163, 184, 0.15);
            color: #64748b;
            font-weight: 500;
        }

        .control-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 12px;
            background: rgba(148, 163, 184, 0.15);
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .control-btn:hover {
            background: rgba(148, 163, 184, 0.25);
            transform: translateY(-1px);
        }

        .control-btn-danger {
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
        }

        .control-btn-danger:hover {
            background: rgba(220, 38, 38, 0.95);
        }

        .control-btn-outline {
            background: transparent;
            border: 1px solid rgba(248, 113, 113, 0.5);
            color: #ef4444;
        }

        .stat-card {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(148, 163, 184, 0.12);
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }

        .viewer-shell {
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .screen-stage {
            min-height: 70vh;
            padding: 28px;
            background: radial-gradient(circle at top, rgba(148, 163, 184, 0.15), transparent 55%);
        }

        .device-canvas {
            border-radius: 18px;
            border: 2px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.45);
            background: #0f172a;
        }

        /* Toast notification animation */
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
            transition: opacity 0.3s, transform 0.3s;
        }

        @media (max-width: 1024px) {
            .control-btn span {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .screen-stage {
                padding: 16px;
                min-height: 60vh;
            }
        }
    </style>
    @endpush

    {{-- Pass data to JavaScript --}}
    @push('scripts')
    <script>
        window.remoteControlConfig = {
            deviceId: {{ $this->record->id }},
            wsUrl: '{{ $this->getRelayServerUrl() }}',
            userId: {{ auth()->id() }},
            sessionToken: '{{ session()->getId() }}',
            canControl: {{ $canControl ? 'true' : 'false' }},
            canRecord: {{ $canRecord ? 'true' : 'false' }},
        };
    </script>
    <script src="{{ asset('js/connection-state-manager.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/remote-control-viewer.js') }}?v={{ time() }}"></script>
    @endpush
    
</x-filament-panels::page>
