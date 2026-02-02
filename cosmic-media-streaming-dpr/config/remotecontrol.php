<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Remote Control Relay Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the WebSocket relay server that handles remote control
    | connections between Android devices and CMS viewers.
    |
    */

    'relay_ws_port' => env('REMOTE_CONTROL_WS_PORT', 3003),
    
    'relay_http_port' => env('REMOTE_CONTROL_HTTP_PORT', 3002),
    
    'relay_host' => env('REMOTE_CONTROL_HOST', 'remote-relay'),
    
    // External WebSocket URL (for browser/APK connections)
    'relay_ws_url' => env('RELAY_WS_URL', 'wss://kiosk.mugshot.dev/remote-control-ws'),
    
    // Internal HTTP URL (for backend-to-relay communication)
    'relay_internal_url' => env('REMOTE_CONTROL_INTERNAL_URL', 'http://remote-relay:3002'),
    
    'use_ssl' => env('APP_ENV') !== 'local',

];
