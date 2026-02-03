#!/usr/bin/env node

/**
 * Continuous mock device for testing
 * Keeps device connected and periodically sends heartbeat
 */

const WebSocket = require('ws');

const RELAY_URL = 'wss://kiosk.mugshot.dev/remote-control-ws';
const DEVICE_ID = 74;
const TOKEN = '8yvL3wk7y6ZM7lqfUipiWm5zen1mQhnhDLDuDScaSWgTgv0hj7r3ORP9DZGW0Qwp';

let ws;
let reconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 5;
const RECONNECT_DELAY = 2000;

function connect() {
    console.log(`\n🚀 [${new Date().toLocaleTimeString()}] Connecting device #${DEVICE_ID} to relay...`);
    
    ws = new WebSocket(RELAY_URL);
    
    ws.on('open', () => {
        console.log(`✅ [${new Date().toLocaleTimeString()}] WebSocket connected`);
        reconnectAttempts = 0;
        
        // Send authentication
        const authMessage = {
            type: 'auth',
            role: 'device',
            deviceId: DEVICE_ID,
            token: TOKEN
        };
        
        ws.send(JSON.stringify(authMessage));
        console.log(`🔐 [${new Date().toLocaleTimeString()}] Authentication message sent`);
        
        // Send periodic heartbeat
        setInterval(() => {
            if (ws.readyState === WebSocket.OPEN) {
                const heartbeat = {
                    type: 'device_status',
                    deviceId: DEVICE_ID,
                    battery: 75,
                    temperature: 35.5,
                    fps: 30
                };
                ws.send(JSON.stringify(heartbeat));
                console.log(`💓 [${new Date().toLocaleTimeString()}] Heartbeat sent`);
            }
        }, 5000);
    });
    
    ws.on('message', (data) => {
        try {
            const message = JSON.parse(data);
            if (message.type === 'auth_success') {
                console.log(`✅ [${new Date().toLocaleTimeString()}] Device authenticated - READY for viewer!`);
            } else if (message.type === 'input_command') {
                console.log(`📥 [${new Date().toLocaleTimeString()}] Received input:`, message.command);
            }
        } catch (e) {}
    });
    
    ws.on('error', (error) => {
        console.error(`❌ [${new Date().toLocaleTimeString()}] Error:`, error.message);
    });
    
    ws.on('close', () => {
        console.log(`🔌 [${new Date().toLocaleTimeString()}] Disconnected - attempting reconnect...`);
        
        if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
            reconnectAttempts++;
            setTimeout(connect, RECONNECT_DELAY);
        } else {
            console.log(`❌ Max reconnection attempts reached`);
            process.exit(1);
        }
    });
}

console.log('🎯 Mock Device Test - CONTINUOUS MODE');
console.log(`Device ID: ${DEVICE_ID}`);
console.log(`Relay: ${RELAY_URL}`);
console.log(`Ctrl+C to stop\n`);

connect();

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n\n👋 Shutting down...');
    if (ws) ws.close();
    process.exit(0);
});
