<!-- 
  Remote Control Viewer Page (Filament Blade Template)
  
  This page displays the Android device screen and allows interaction.
  
  File location: 
  resources/views/filament/pages/remote-control-viewer.blade.php
-->

<x-filament-panels::page>
    <div class="remote-control-container">
        
        {{-- Header with device info and controls --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4">
            <div class="flex items-center justify-between">
                {{-- Device Info --}}
                <div class="flex items-center space-x-4">
                    <div class="w-3 h-3 rounded-full {{ $this->record->status === 'Connected' ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></div>
                    <div>
                        <h2 class="text-lg font-semibold">{{ $this->record->name }}</h2>
                        <p class="text-sm text-gray-500">
                            IP: {{ $this->record->ip_address }} | Port: {{ $this->record->remote_control_port }} | 
                            Status: <span id="connection-status">Connecting...</span>
                        </p>
                    </div>
                </div>
                
                {{-- Control Buttons --}}
                <div class="flex items-center space-x-2">
                    <button 
                        type="button"
                        id="btn-back"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center space-x-2"
                        title="Back Button">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back</span>
                    </button>
                    
                    <button 
                        type="button"
                        id="btn-home"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center space-x-2"
                        title="Home Button">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Home</span>
                    </button>
                    
                    <button 
                        type="button"
                        id="btn-keyboard"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center space-x-2"
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
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center space-x-2"
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
                        class="px-4 py-2 bg-gray-200 hover:bg-red-200 rounded-lg flex items-center space-x-2"
                        title="Disconnect">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Disconnect</span>
                    </button>
                </div>
            </div>
            
            {{-- Stats Bar --}}
            <div class="mt-3 flex items-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center space-x-2">
                    <span class="font-medium">FPS:</span>
                    <span id="stat-fps">0</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-medium">Latency:</span>
                    <span id="stat-latency">- ms</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-medium">Resolution:</span>
                    <span id="stat-resolution">{{ $this->record->screen_resolution ?? 'Unknown' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-medium">Session:</span>
                    <span id="stat-duration">00:00:00</span>
                </div>
            </div>
        </div>
        
        {{-- Screen Viewer --}}
        <div class="bg-gray-900 rounded-lg shadow overflow-hidden relative">
            <div class="flex justify-center items-center" style="min-height: 600px;">
                {{-- Canvas for device screen --}}
                <canvas 
                    id="device-screen"
                    class="cursor-pointer border-2 border-gray-700"
                    width="1080"
                    height="1920"
                    style="max-width: 100%; height: auto;">
                </canvas>
                
                {{-- Loading Overlay --}}
                <div id="loading-overlay" class="absolute inset-0 flex items-center justify-center bg-gray-900 bg-opacity-90">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-white mx-auto mb-4"></div>
                        <p class="text-white text-lg">Connecting to device...</p>
                        <p class="text-gray-400 text-sm mt-2">Please wait</p>
                    </div>
                </div>
                
                {{-- Disconnected Overlay --}}
                <div id="disconnected-overlay" class="absolute inset-0 hidden items-center justify-center bg-gray-900 bg-opacity-90">
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
    
    {{-- Pass data to JavaScript --}}
    @push('scripts')
    <script>
        window.remoteControlConfig = {
            deviceId: {{ $this->record->id }},
            deviceToken: '{{ $this->record->token }}',
            wsUrl: '{{ $this->getRelayServerUrl() }}',
            userId: {{ auth()->id() }},
            canControl: {{ $canControl ? 'true' : 'false' }},
            canRecord: {{ $canRecord ? 'true' : 'false' }},
        };
    </script>
    <script src="{{ asset('js/remote-control-viewer.js') }}" defer></script>
    @endpush
    
</x-filament-panels::page>
