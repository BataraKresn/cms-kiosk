#!/usr/bin/env node

/**
 * Test script to simulate Android device connecting to relay server
 * Usage: node test-device-connection.js [deviceId] [token]
 */

const WebSocket = require('ws');

const RELAY_URL = 'wss://kiosk.mugshot.dev/remote-control-ws';
const DEVICE_ID = parseInt(process.argv[2] || 74);
const TOKEN = process.argv[3] || '8yvL3wk7y6ZM7lqfUipiWm5zen1mQhnhDLDuDScaSWgTgv0hj7r3ORP9DZGW0Qwp';

console.log(`🚀 Simulating device #${DEVICE_ID} connection to relay server...`);
console.log(`📍 Relay URL: ${RELAY_URL}`);

const ws = new WebSocket(RELAY_URL);

ws.on('open', () => {
    console.log('✅ WebSocket connected');
    
    // Send device authentication message
    const authMessage = {
        type: 'auth',
        role: 'device',
        deviceId: DEVICE_ID,
        token: TOKEN
    };
    
    console.log('🔐 Sending authentication message:', authMessage);
    ws.send(JSON.stringify(authMessage));
});

ws.on('message', (data) => {
    try {
        const message = JSON.parse(data);
        console.log('📨 Received message:', message);
        
        if (message.type === 'auth_success') {
            console.log('✅ Device authenticated successfully!');
            
            // Send heartbeat
            setTimeout(() => {
                const heartbeat = {
                    type: 'heartbeat',
                    deviceId: DEVICE_ID,
                    battery: 75,
                    temperature: 35.5
                };
                console.log('💓 Sending heartbeat...');
                ws.send(JSON.stringify(heartbeat));
            }, 1000);
            
            // Simulate frame sending (minimal test)
            setTimeout(() => {
                const frameInfo = {
                    type: 'frame',
                    width: 1080,
                    height: 2400,
                    fps: 30,
                    size: 1024
                };
                console.log('🎬 Sending frame info...');
                ws.send(JSON.stringify(frameInfo));
            }, 2000);
            
            // Keep connection alive for 10 seconds
            setTimeout(() => {
                console.log('⏱️  Test duration complete, closing connection');
                ws.close();
            }, 10000);
        }
    } catch (error) {
        console.log('📨 Received text:', data);
    }
});

ws.on('error', (error) => {
    console.error('❌ WebSocket error:', error.message);
});

ws.on('close', () => {
    console.log('🔌 WebSocket disconnected');
});
