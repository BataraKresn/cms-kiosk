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

// Simple logger wrapper - logs errors/warnings always, info only in dev
const IS_PRODUCTION = process.env.NODE_ENV === 'production';
const ENABLE_DEBUG = process.env.LOG_LEVEL === 'debug' || process.env.DEBUG === '1';
const logger = {
    info: (...args) => console.log('[INFO]', ...args), // Always log info (startup messages important)
    warn: (...args) => console.warn('[WARN]', ...args),
    error: (...args) => console.error('[ERROR]', ...args),
    debug: (...args) => (ENABLE_DEBUG || !IS_PRODUCTION) && console.log('[DEBUG]', ...args) // Debug in dev or if explicitly enabled
};

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
 * Generate unique ID
 */
function generateId() {
    return Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
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
        
        logger.info('✅ Database connection pool created');
        
        // Test connection
        const connection = await dbPool.getConnection();
        logger.info('✅ Database connection test successful');
        connection.release();
        
    } catch (error) {
        logger.error('❌ Database connection failed:', error);
        process.exit(1);
    }
}

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
    
    logger.debug(`📨 Message received: type=${type}, role=${metadata.role}, deviceId=${metadata.deviceId}`);
    
    // Route message based on type
    switch (type) {
        case 'frame':
            handleFrame(ws, message);
            break;
            
        case 'input_command':
            logger.debug(`📨 Input command received: ${JSON.stringify(message).substring(0, 100)}`);
            handleInputCommand(ws, message);
            break;
            
        case 'control_command':
            handleControlCommand(ws, message);
            break;
            
        case 'ping':
            ws.send(JSON.stringify({ type: 'pong' }));
            break;
        
        case 'device_status':
        case 'heartbeat':
            // Device heartbeat - just acknowledge
            // Device stats: battery, temperature, fps, etc.
            logger.debug('💓 Device heartbeat from:', metadata.deviceId);
            break;
            
        default:
            logger.warn('⚠️ Unknown message type:', type);
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
            logger.info(`🔍 Device auth query: token=${token.substring(0, 20)}...`);
            logger.info(`📝 Token length: ${token.length}`);
            [dbRecord] = await dbPool.query(
                'SELECT id, name, remote_control_enabled, token as db_token FROM remotes WHERE token = ? AND deleted_at IS NULL',
                [token]
            );
            
            logger.info(`📊 DB query result: found ${dbRecord.length} records`);
            if (dbRecord.length > 0) {
                logger.info(`✅ Device record found: id=${dbRecord[0].id}, name=${dbRecord[0].name}, enabled=${dbRecord[0].remote_control_enabled}`);
                const tokenMatch = dbRecord[0].db_token === token;
                logger.info(`🔐 Token match: ${tokenMatch} (DB length: ${dbRecord[0].db_token.length}, Received length: ${token.length})`);
            } else {
                logger.warn(`⚠️ No device record found for token=${token.substring(0, 20)}...`);
                // Try to see if token exists at all
                const [checkToken] = await dbPool.query(
                    'SELECT id, token FROM remotes WHERE token LIKE ?',
                    [`${token.substring(0, 30)}%`]
                );
                if (checkToken.length > 0) {
                    logger.warn(`💡 Found similar token in DB: id=${checkToken[0].id}, token=${checkToken[0].token.substring(0, 30)}...`);
                }
            }
            
            if (dbRecord.length > 0 && dbRecord[0].remote_control_enabled) {
                isValid = true;
                logger.info(`✅ Device authentication VALID`);
            } else {
                logger.warn(`❌ Device authentication FAILED: records=${dbRecord.length}, enabled=${dbRecord[0]?.remote_control_enabled}`);
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
        
        logger.info(`✅ Authenticated: ${role} for device ${deviceId}`);
        
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
                logger.info(`📡 Device ${deviceId} status updated to Connected`);
            } catch (err) {
                logger.error('Error updating device status:', err);
            }
        }
        
        // If viewer, create session record
        if (role === 'viewer') {
            await createSession(metadata);
        }
        
    } catch (error) {
        logger.error('❌ Authentication error:', error.message);
        logger.error('   Stack:', error.stack);
        sendError(ws, 'Authentication error');
        ws.close();
    }
}

/**
 * Add client to room
 */
function addToRoom(ws, role, deviceId) {
    const existingRoom = rooms.get(deviceId);
    
    if (!rooms.has(deviceId)) {
        rooms.set(deviceId, {
            device: null,
            viewers: []
        });
        logger.debug(`📍 New room created: ${deviceId}`);
    } else {
        logger.debug(`📍 Using existing room: ${deviceId} (device=${!!existingRoom.device}, viewers=${existingRoom.viewers.length})`);
    }
    
    const room = rooms.get(deviceId);
    
    if (role === 'device') {
        // Only one device per room
        if (room.device && room.device !== ws) {
            logger.warn(`⚠️ Device already connected to room ${deviceId}, closing old connection`);
            room.device.close();
        }
        room.device = ws;
        const wsId = clientMetadata.get(ws)?.id;
        logger.info(`📱 Device added to room: ${deviceId} (ws_id=${wsId}, now ${room.viewers.length} viewer(s) in room)`);
        
    } else if (role === 'viewer') {
        room.viewers.push(ws);
        const wsId = clientMetadata.get(ws)?.id;
        logger.info(`👁️ Viewer added to room: ${deviceId} (ws_id=${wsId}, total: ${room.viewers.length}, device_connected=${!!room.device})`);
    }
}

/**
 * Handle video frame from device
 */
function handleFrame(ws, message) {
    const metadata = clientMetadata.get(ws);
    const { deviceId } = metadata;
    const room = rooms.get(deviceId);
    
    if (!room) {
        logger.warn(`⚠️ Frame received but no room found for device: ${deviceId}`);
        return;
    }
    
    // Update statistics
    updateStats(deviceId, 'framesSent');
    
    // Log frame broadcast (only in non-production or debug mode)
    const viewerCount = room.viewers.length;
    if (viewerCount === 0) {
        logger.debug(`📹 Frame received but NO viewers connected for device: ${deviceId}`);
    } else {
        logger.debug(`📹 Broadcasting frame to ${viewerCount} viewer(s) for device: ${deviceId}`);
    }
    
    // Broadcast frame to all viewers in the room
    let broadcastCount = 0;
    room.viewers.forEach(viewer => {
        if (viewer.readyState === WebSocket.OPEN) {
            try {
                viewer.send(JSON.stringify(message));
                broadcastCount++;
            } catch (error) {
                logger.error(`❌ Error sending frame to viewer:`, error);
            }
        } else {
            logger.warn(`⚠️ Viewer connection not OPEN (state: ${viewer.readyState}), skipping frame`);
        }
    });
    
    // Log if broadcast failed
    if (broadcastCount === 0 && viewerCount > 0) {
        logger.warn(`⚠️ Frame received but failed to send to any of ${viewerCount} viewer(s)`);
    }
    
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
    logger.debug(`🔍 Input handler: deviceId=${deviceId}, room exists=${!!room}, device exists=${room?.device ? 'yes' : 'no'}, viewers=${room?.viewers?.length || 0}`);
    if (room?.device) {
        logger.debug(`   room.device.readyState=${room.device.readyState}, ws from metadata same=${room.device === clientMetadata.get(ws)}`);
    }
    
    if (!room || !room.device) {
        logger.warn(`⚠️ Device ${deviceId} not in room for input command. Rooms: ${Array.from(rooms.keys()).join(',')}`);
        sendError(ws, 'Device not connected');
        return;
    }
    
    // Update statistics
    updateStats(deviceId, 'inputsSent');
    
    // Forward command to device
    if (room.device.readyState === WebSocket.OPEN) {
        room.device.send(JSON.stringify(message));
        // Input logging disabled in production (high frequency - every touch/swipe)
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
        logger.debug(`⚙️ Control command sent to device: ${deviceId}`);
    }
}

/**
 * Handle client disconnect
 */
async function handleDisconnect(ws) {
    const metadata = clientMetadata.get(ws);
    if (!metadata) {
        logger.debug(`📉 Disconnect from unknown client (no metadata)`);
        return;
    }
    
    const { role, deviceId, authenticated, id: wsId, connId } = metadata;
    
    if (!authenticated) {
        logger.debug(`📉 ${connId || '?'}: Unauthenticated connection closed (ws_id=${wsId})`);
        return;
    }
    
    logger.warn(`🔌 ${connId || '?'}: Disconnect: ${role} from device ${deviceId} (ws_id=${wsId})`);
    
    
    const room = rooms.get(deviceId);
    if (!room) return;
    
    if (role === 'device') {
        room.device = null;
        logger.info(`📱 Device disconnected from room: ${deviceId}`);
        
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
            logger.info(`📡 Device ${deviceId} status updated to Disconnected`);
        } catch (err) {
            logger.error('Error updating device status:', err);
        }
        
    } else if (role === 'viewer') {
        room.viewers = room.viewers.filter(v => v !== ws);
        logger.info(`👁️ Viewer disconnected from room: ${deviceId} (remaining: ${room.viewers.length})`);
        
        // End session in database
        await endSession(metadata);
    }
    
    // Clean up empty rooms
    // DISABLED: Keep rooms alive even after device/viewers disconnect
    // This prevents the race condition where viewer reconnects to a deleted room
    // Only delete if BOTH device is gone AND no viewers connected
    // if (!room.device && room.viewers.length === 0) {
    //     rooms.delete(deviceId);
    //     sessionStats.delete(deviceId);
    //     logger.info(`🧹 Room cleaned up: ${deviceId}`);
    // }
    
    // If device disconnects but viewers still connected, notify them
    if (!room.device && room.viewers.length > 0) {
        logger.warn(`⚠️ Device ${deviceId} disconnected but ${room.viewers.length} viewer(s) still in room`);
        room.viewers.forEach(viewer => {
            if (viewer.readyState === WebSocket.OPEN) {
                viewer.send(JSON.stringify({
                    type: 'device_disconnected',
                    message: 'Device has disconnected'
                }));
            }
        });
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
        
        logger.info(`📝 Session created: ${result.insertId}`);
        
    } catch (error) {
        logger.error('❌ Error creating session:', error);
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
        
        logger.info(`📝 Session ended: ${metadata.sessionId}`);
        
    } catch (error) {
        logger.error('❌ Error ending session:', error);
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
        ).catch(err => logger.error('DB update error:', err));
    }
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
    console.log('🚀 Starting Remote Control Relay Server...');
    console.log('🌍 Environment:', process.env.NODE_ENV || 'development');
    console.log('🔌 WebSocket Port:', WS_PORT);
    console.log('🌐 HTTP Port:', HTTP_PORT);
    
    await initDatabase();
    
    server.listen(HTTP_PORT, () => {
        logger.info(`🌐 HTTP server running on port ${HTTP_PORT}`);
        console.log(`✅ HTTP server listening on http://0.0.0.0:${HTTP_PORT}`);
    });
    
    logger.info(`🔌 WebSocket server running on port ${WS_PORT}`);
    logger.info(`✅ Remote Control Relay Server started`);
    console.log(`✅ WebSocket server listening on ws://0.0.0.0:${WS_PORT}`);
    console.log(`✅ Relay Server READY - Waiting for connections...`);
    
    // Setup WebSocket connection handler AFTER all functions are defined
    wss.on('connection', (ws, req) => {
        const connId = Math.random().toString(36).substring(7);
        logger.info(`📡 NEW CONNECTION: ${connId} from ${req.socket.remoteAddress}`);
        
        // Initialize client metadata
        clientMetadata.set(ws, {
            id: generateId(),
            connectedAt: Date.now(),
            ip: req.socket.remoteAddress,
            authenticated: false,
            connId: connId
        });
        
        // Set connection timeout for authentication
        const authTimeout = setTimeout(() => {
            if (!clientMetadata.get(ws).authenticated) {
                logger.warn(`⏱️ ${connId}: Authentication timeout, closing connection`);
                ws.close(4001, 'Authentication timeout');
            }
        }, 30000); // 30 seconds
        
        // Handle incoming messages
        ws.on('message', async (data) => {
            try {
                const dataStr = data.toString();
                const metadata = clientMetadata.get(ws);
                logger.debug(`📨 ${metadata?.connId || '?'}: RAW message from ${metadata?.role || 'unauthenticated'}: ${dataStr.substring(0, 80)}`);
                
                // Handle plain text heartbeat from APK (backward compatibility)
                if (dataStr === 'ping') {
                    ws.send(JSON.stringify({ type: 'pong' }));
                    logger.debug(`💓 ${metadata?.connId || '?'}: Heartbeat pong sent`);
                    return;
                }
                
                // Ignore non-JSON messages to avoid sending type:error
                const trimmed = dataStr.trim();
                if (!trimmed.startsWith('{') && !trimmed.startsWith('[')) {
                    logger.debug('ℹ️ Ignoring non-JSON message');
                    return;
                }
                
                const message = JSON.parse(trimmed);
                await handleMessage(ws, message, authTimeout);
            } catch (error) {
                const metadata = clientMetadata.get(ws);
                logger.error(`❌ Error handling message from ${metadata?.role || 'unknown'}: ${error.message}`);
                logger.error(`   Stack: ${error.stack}`);
                // Do not send type:error to client; just ignore malformed payloads
            }
        });
        
        // Handle connection close
        ws.on('close', () => {
            const metadata = clientMetadata.get(ws);
            const connId = metadata?.connId || '?';
            logger.warn(`📉 ${connId}: Connection closed (${metadata?.role || 'unknown'} / device ${metadata?.deviceId || '?'})`);
            clearTimeout(authTimeout);
            handleDisconnect(ws);
        });
        
        // Handle errors
        ws.on('error', (error) => {
            const metadata = clientMetadata.get(ws);
            const connId = metadata?.connId || '?';
            logger.error(`❌ ${connId}: WebSocket error from ${metadata?.role || 'unknown'}: ${error.message}`);
        });
    });
}

start().catch(error => {
    logger.error('❌ Fatal error:', error);
    process.exit(1);
});

// Graceful shutdown
process.on('SIGINT', async () => {
    logger.info('\n🛑 Shutting down gracefully...');
    
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