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
    
    'relay_host' => env('REMOTE_CONTROL_HOST', 'kiosk.mugshot.dev'),
    
    'relay_ws_url' => env('RELAY_WS_URL', 'wss://kiosk.mugshot.dev/remote-control-ws'),
    
    'use_ssl' => env('APP_ENV') !== 'local',

];
