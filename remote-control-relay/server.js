/**
 * Remote Control Relay Server (Node.js + WebSocket)
 * 
 * This server acts as a relay/hub between Android devices and CMS viewers.
 * 
 * Architecture:
 * - Android devices connect as "publishers" (send video frames)
 * - CMS viewers connect as "subscribers" (receive video, send input)
 * - Server maintains room-based routing (one device = one room)
 * 
 * Features:
 * - WebSocket connections (ws/wss)
 * - Room-based routing
 * - Authentication via device token
 * - Session management
 * - Heartbeat monitoring
 * - Connection statistics
 * 
 * @author Cosmic Development Team
 * @version 1.0.0 (POC)
 */

const WebSocket = require('ws');
const express = require('express');
const http = require('http');
const mysql = require('mysql2/promise');
require('dotenv').config();

// Configuration
const HTTP_PORT = process.env.HTTP_PORT || 3002;
const WS_PORT = process.env.WS_PORT || 3003;
const DB_HOST = process.env.DB_HOST || 'localhost';
const DB_PORT = process.env.DB_PORT || 3306;
const DB_USER = process.env.DB_USER || 'platform_user';
const DB_PASSWORD = process.env.DB_PASSWORD || 'password';
const DB_NAME = process.env.DB_NAME || 'platform';

// Express app for HTTP endpoints
const app = express();
app.use(express.json());

// Create HTTP server
const server = http.createServer(app);

// Create WebSocket server
const wss = new WebSocket.Server({ port: WS_PORT });

// Database connection pool
let dbPool;

// Connected clients storage
// Structure: { deviceId: { device: WebSocket, viewers: [WebSocket] } }
const rooms = new Map();

// Client metadata storage
// Structure: { WebSocket: { id, role, deviceId, userId, ... } }
const clientMetadata = new WeakMap();

// Session statistics
const sessionStats = new Map();

/**
 * Initialize database connection
 */
async function initDatabase() {
    try {
        dbPool = mysql.createPool({
            host: DB_HOST,
            port: DB_PORT,
            user: DB_USER,
            password: DB_PASSWORD,
            database: DB_NAME,
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0
        });
        
        console.log('✅ Database connection pool created');
        
        // Test connection
        const connection = await dbPool.getConnection();
        console.log('✅ Database connection test successful');
        connection.release();
        
    } catch (error) {
        console.error('❌ Database connection failed:', error);
        process.exit(1);
    }
}

/**
 * Handle new WebSocket connection
 */
wss.on('connection', (ws, req) => {
    console.log('📱 New WebSocket connection from:', req.socket.remoteAddress);
    
    // Initialize client metadata
    clientMetadata.set(ws, {
        id: generateId(),
        connectedAt: Date.now(),
        ip: req.socket.remoteAddress,
        authenticated: false
    });
    
    // Set connection timeout for authentication
    const authTimeout = setTimeout(() => {
        if (!clientMetadata.get(ws).authenticated) {
            console.log('⏱️ Authentication timeout, closing connection');
            ws.close(4001, 'Authentication timeout');
        }
    }, 30000); // 30 seconds
    
    // Handle incoming messages
    ws.on('message', async (data) => {
        try {
            const message = JSON.parse(data.toString());
            await handleMessage(ws, message, authTimeout);
        } catch (error) {
            console.error('❌ Error handling message:', error);
            sendError(ws, 'Invalid message format');
        }
    });
    
    // Handle connection close
    ws.on('close', () => {
        clearTimeout(authTimeout);
        handleDisconnect(ws);
    });
    
    // Handle errors
    ws.on('error', (error) => {
        console.error('❌ WebSocket error:', error);
    });
});

/**
 * Handle incoming message from client
 */
async function handleMessage(ws, message, authTimeout) {
    const metadata = clientMetadata.get(ws);
    const { type } = message;
    
    // Handle authentication first
    if (type === 'auth') {
        await handleAuthentication(ws, message, authTimeout);
        return;
    }
    
    // Require authentication for all other messages
    if (!metadata.authenticated) {
        sendError(ws, 'Not authenticated');
        return;
    }
    
    // Route message based on type
    switch (type) {
        case 'frame':
            handleFrame(ws, message);
            break;
            
        case 'input_command':
            handleInputCommand(ws, message);
            break;
            
        case 'control_command':
            handleControlCommand(ws, message);
            break;
            
        case 'ping':
            ws.send('pong');
            break;
            
        default:
            console.warn('⚠️ Unknown message type:', type);
    }
}

/**
 * Handle authentication message
 */
async function handleAuthentication(ws, message, authTimeout) {
    try {
        const { role, deviceId, token, userId } = message;
        
        // Validate required fields
        if (!role || !token) {
            sendError(ws, 'Missing required authentication fields');
            ws.close();
            return;
        }
        
        // Verify token against database
        let isValid = false;
        let dbRecord;
        
        if (role === 'device') {
            // Authenticate Android device
            [dbRecord] = await dbPool.query(
                'SELECT id, name, remote_control_enabled FROM remotes WHERE token = ? AND deleted_at IS NULL',
                [token]
            );
            
            if (dbRecord.length > 0 && dbRecord[0].remote_control_enabled) {
                isValid = true;
            }
            
        } else if (role === 'viewer') {
            // Authenticate CMS viewer (user)
            // Check both user authentication and permissions
            const [userRecord] = await dbPool.query(
                'SELECT id FROM users WHERE id = ?',
                [userId]
            );
            
            const [permRecord] = await dbPool.query(
                `SELECT can_view, can_control FROM remote_permissions 
                 WHERE user_id = ? AND (remote_id = ? OR remote_id IS NULL)
                 AND deleted_at IS NULL
                 LIMIT 1`,
                [userId, deviceId]
            );
            
            if (userRecord.length > 0 && permRecord.length > 0 && permRecord[0].can_view) {
                isValid = true;
                dbRecord = permRecord;
            }
        }
        
        if (!isValid) {
            sendError(ws, 'Authentication failed');
            ws.close();
            return;
        }
        
        // Clear auth timeout
        clearTimeout(authTimeout);
        
        // Update client metadata
        const metadata = clientMetadata.get(ws);
        metadata.authenticated = true;
        metadata.role = role;
        metadata.deviceId = deviceId;
        metadata.userId = userId;
        metadata.token = token;
        
        if (role === 'device') {
            metadata.deviceName = dbRecord[0].name;
        } else if (role === 'viewer') {
            metadata.canControl = dbRecord[0].can_control;
        }
        
        // Add to appropriate room
        addToRoom(ws, role, deviceId);
        
        // Send success response
        ws.send(JSON.stringify({
            type: 'auth_success',
            message: 'Authentication successful',
            role: role,
            deviceId: deviceId
        }));
        
        console.log(`✅ Authenticated: ${role} for device ${deviceId}`);
        
        // If device, update database status to Connected
        if (role === 'device') {
            try {
                await dbPool.query(
                    `UPDATE remotes SET 
                     status = 'Connected',
                     last_seen_at = NOW()
                     WHERE id = ?`,
                    [dbRecord[0].id]
                );
                console.log(`📡 Device ${deviceId} status updated to Connected`);
            } catch (err) {
                console.error('Error updating device status:', err);
            }
        }
        
        // If viewer, create session record
        if (role === 'viewer') {
            await createSession(metadata);
        }
        
    } catch (error) {
        console.error('❌ Authentication error:', error);
        sendError(ws, 'Authentication error');
        ws.close();
    }
}

/**
 * Add client to room
 */
function addToRoom(ws, role, deviceId) {
    if (!rooms.has(deviceId)) {
        rooms.set(deviceId, {
            device: null,
            viewers: []
        });
    }
    
    const room = rooms.get(deviceId);
    
    if (role === 'device') {
        // Only one device per room
        if (room.device) {
            console.log('⚠️ Device already connected, closing old connection');
            room.device.close();
        }
        room.device = ws;
        console.log(`📱 Device added to room: ${deviceId}`);
        
    } else if (role === 'viewer') {
        room.viewers.push(ws);
        console.log(`👁️ Viewer added to room: ${deviceId} (total: ${room.viewers.length})`);
    }
}

/**
 * Handle video frame from device
 */
function handleFrame(ws, message) {
    const metadata = clientMetadata.get(ws);
    const { deviceId } = metadata;
    const room = rooms.get(deviceId);
    
    if (!room) return;
    
    // Update statistics
    updateStats(deviceId, 'framesSent');
    
    // Broadcast frame to all viewers in the room
    room.viewers.forEach(viewer => {
        if (viewer.readyState === WebSocket.OPEN) {
            viewer.send(JSON.stringify(message));
        }
    });
    
    // Update last frame timestamp in database (throttled)
    throttledDbUpdate(deviceId);
}

/**
 * Handle input command from viewer
 */
function handleInputCommand(ws, message) {
    const metadata = clientMetadata.get(ws);
    const { deviceId, canControl } = metadata;
    
    // Check permission
    if (!canControl) {
        sendError(ws, 'No control permission');
        return;
    }
    
    const room = rooms.get(deviceId);
    if (!room || !room.device) {
        sendError(ws, 'Device not connected');
        return;
    }
    
    // Update statistics
    updateStats(deviceId, 'inputsSent');
    
    // Forward command to device
    if (room.device.readyState === WebSocket.OPEN) {
        room.device.send(JSON.stringify(message));
        console.log(`🎮 Input command sent to device: ${deviceId}`);
    }
}

/**
 * Handle control command (adjust quality, FPS, etc.)
 */
function handleControlCommand(ws, message) {
    const metadata = clientMetadata.get(ws);
    const { deviceId, canControl } = metadata;
    
    if (!canControl) {
        sendError(ws, 'No control permission');
        return;
    }
    
    const room = rooms.get(deviceId);
    if (!room || !room.device) {
        sendError(ws, 'Device not connected');
        return;
    }
    
    // Forward control command to device
    if (room.device.readyState === WebSocket.OPEN) {
        room.device.send(JSON.stringify(message));
        console.log(`⚙️ Control command sent to device: ${deviceId}`);
    }
}

/**
 * Handle client disconnect
 */
async function handleDisconnect(ws) {
    const metadata = clientMetadata.get(ws);
    if (!metadata) return;
    
    const { role, deviceId, authenticated } = metadata;
    
    if (!authenticated) return;
    
    console.log(`🔌 Disconnect: ${role} from device ${deviceId}`);
    
    const room = rooms.get(deviceId);
    if (!room) return;
    
    if (role === 'device') {
        room.device = null;
        console.log(`📱 Device disconnected from room: ${deviceId}`);
        
        // Notify all viewers
        room.viewers.forEach(viewer => {
            if (viewer.readyState === WebSocket.OPEN) {
                viewer.send(JSON.stringify({
                    type: 'device_disconnected',
                    message: 'Device has disconnected'
                }));
            }
        });
        
        // Update database status to Disconnected
        try {
            await dbPool.query(
                `UPDATE remotes SET 
                 status = 'Disconnected',
                 last_seen_at = NOW()
                 WHERE id = ?`,
                [deviceId]
            );
            console.log(`📡 Device ${deviceId} status updated to Disconnected`);
        } catch (err) {
            console.error('Error updating device status:', err);
        }
        
    } else if (role === 'viewer') {
        room.viewers = room.viewers.filter(v => v !== ws);
        console.log(`👁️ Viewer disconnected from room: ${deviceId} (remaining: ${room.viewers.length})`);
        
        // End session in database
        await endSession(metadata);
    }
    
    // Clean up empty rooms
    if (!room.device && room.viewers.length === 0) {
        rooms.delete(deviceId);
        sessionStats.delete(deviceId);
        console.log(`🧹 Room cleaned up: ${deviceId}`);
    }
}

/**
 * Create session record in database
 */
async function createSession(metadata) {
    try {
        const sessionToken = generateId();
        
        const [result] = await dbPool.query(
            `INSERT INTO remote_sessions 
             (remote_id, user_id, session_token, status, viewer_ip, started_at)
             VALUES (?, ?, ?, 'active', ?, NOW())`,
            [metadata.deviceId, metadata.userId, sessionToken, metadata.ip]
        );
        
        metadata.sessionId = result.insertId;
        metadata.sessionToken = sessionToken;
        
        console.log(`📝 Session created: ${result.insertId}`);
        
    } catch (error) {
        console.error('❌ Error creating session:', error);
    }
}

/**
 * End session record in database
 */
async function endSession(metadata) {
    if (!metadata.sessionId) return;
    
    try {
        const stats = sessionStats.get(metadata.deviceId) || {};
        
        await dbPool.query(
            `UPDATE remote_sessions SET 
             status = 'ended',
             ended_at = NOW(),
             duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW()),
             frames_sent = ?,
             input_commands_sent = ?
             WHERE id = ?`,
            [stats.framesSent || 0, stats.inputsSent || 0, metadata.sessionId]
        );
        
        console.log(`📝 Session ended: ${metadata.sessionId}`);
        
    } catch (error) {
        console.error('❌ Error ending session:', error);
    }
}

/**
 * Update statistics
 */
function updateStats(deviceId, metric) {
    if (!sessionStats.has(deviceId)) {
        sessionStats.set(deviceId, {
            framesSent: 0,
            inputsSent: 0,
            lastDbUpdate: 0
        });
    }
    
    const stats = sessionStats.get(deviceId);
    stats[metric] = (stats[metric] || 0) + 1;
}

/**
 * Throttled database update (max once per 5 seconds)
 */
const lastDbUpdate = new Map();
function throttledDbUpdate(deviceId) {
    const now = Date.now();
    const last = lastDbUpdate.get(deviceId) || 0;
    
    if (now - last > 5000) {
        lastDbUpdate.set(deviceId, now);
        dbPool.query(
            'UPDATE remotes SET last_frame_at = NOW() WHERE id = ?',
            [deviceId]
        ).catch(err => console.error('DB update error:', err));
    }
}

/**
 * Send error message to client
 */
function sendError(ws, message) {
    if (ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({
            type: 'error',
            message: message
        }));
    }
}

/**
 * Generate unique ID
 */
function generateId() {
    return Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
}

// HTTP endpoints for monitoring
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        uptime: process.uptime(),
        rooms: rooms.size,
        timestamp: Date.now()
    });
});

app.get('/stats', (req, res) => {
    const stats = [];
    rooms.forEach((room, deviceId) => {
        stats.push({
            deviceId: deviceId,
            hasDevice: !!room.device,
            viewerCount: room.viewers.length,
            stats: sessionStats.get(deviceId) || {}
        });
    });
    
    res.json(stats);
});

// Initialize and start server
async function start() {
    await initDatabase();
    
    server.listen(HTTP_PORT, () => {
        console.log(`🌐 HTTP server running on port ${HTTP_PORT}`);
    });
    
    console.log(`🔌 WebSocket server running on port ${WS_PORT}`);
    console.log(`✅ Remote Control Relay Server started`);
}

start().catch(error => {
    console.error('❌ Fatal error:', error);
    process.exit(1);
});

// Graceful shutdown
process.on('SIGINT', async () => {
    console.log('\n🛑 Shutting down gracefully...');
    
    // Close all WebSocket connections
    wss.clients.forEach(client => {
        client.close();
    });
    
    // Close database pool
    if (dbPool) {
        await dbPool.end();
    }
    
    process.exit(0);
});
