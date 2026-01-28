const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 3335 });

let connectedDevices = []; // To store connected clients

wss.on('connection', (ws) => {

    // Add the device to the list of connected devices
    connectedDevices.push(ws);

    // Listen for messages from the device
    ws.on('message', (message) => {
        // Broadcast the message to all other connected devices
        connectedDevices.forEach(device => {
            if (device !== ws) {
                device.send(message.toString('utf-8'));
            }
        });
    });

    // Handle disconnection
    ws.on('close', () => {
        connectedDevices = connectedDevices.filter(device => device !== ws);
        console.log('Device disconnected');
    });
});

console.log('WebSocket server is running on ws://localhost:3335/ws');
