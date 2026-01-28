const WebSocket = require('ws');
const { parse } = require('url');
const pool = require('./database'); // Assuming you have a pool setup for DB queries

const wss = new WebSocket.Server({ port: 3336 });

let connectedDevices = []; // To store connected clients

wss.on('connection', (ws, req) => {
    // Parse query parameters from the connection URL
    const queryParams = parse(req.url, true).query;
    const deviceUrl = queryParams.url; // Extract the 'url' parameter

    // Remove the "/ws?url=" prefix from the URL if it exists
    const cleanDeviceUrl = deviceUrl ? deviceUrl.replace(/^\/ws\?url=/, '') : deviceUrl;
    console.log('Cleaned URL:', cleanDeviceUrl);

    // Extract the domain and port (e.g., 10.0.2.15:5800) from the URL
    const parsedUrl = new URL(cleanDeviceUrl); // Parse the URL
    const domainAndPort = `${parsedUrl.hostname}:${parsedUrl.port}`; // Get domain and port

    console.log('Extracted Domain and Port:', domainAndPort);

    // Add the device to the list of connected devices
    connectedDevices.push({ ws, url: cleanDeviceUrl });

    // Listen for messages from the device
    ws.on('message', async (message) => {
        try {
            const result = JSON.parse(message.toString('utf-8'));

            // Assuming result.token is the identifier, you may change this to match your message structure
            const [updateResult] = await pool.query(
                `UPDATE remotes SET status = "Connected" WHERE url = ?`,
                [result.token] // Use result.token or change as needed
            );

            if (updateResult.affectedRows > 0) {
                console.log(`Updated status to "Connected" for URL: ${result.token}`);
            }

            // Broadcast the message to all other connected devices
            connectedDevices.forEach(device => {
                if (device.ws !== ws) {
                    device.ws.send(message.toString('utf-8'));
                }
            });

        } catch (error) {
            console.error("Error handling message:", error.message);
        }
    });

    // Handle disconnection
    ws.on('close', async () => {
        // Remove the device from the connectedDevices array
        connectedDevices = connectedDevices.filter(device => device.ws !== ws);
        console.log(`Device with URL ${domainAndPort} disconnected`);

        try {
            // Update the status of the disconnected device in the database
            const [updateResult] = await pool.query(
                `UPDATE remotes SET status = "Disconnected" WHERE url like ?`,
                [`%${domainAndPort}%`] // Use the correct device URL for status update
            );

            if (updateResult.affectedRows > 0) {
                console.log(`Updated status to "Disconnected" for URL: ${domainAndPort}`);
            }
        } catch (error) {
            console.error("Error updating status to Disconnected:", error.message);
        }
    });
});

console.log('WebSocket server is running on ws://localhost:3336/ws_status_device');
