# Remote Control Relay Server

WebSocket relay server for routing video frames and input commands between Android devices and CMS viewers.

## Features

- ✅ WebSocket-based communication
- ✅ Room-based routing (1 device = 1 room)
- ✅ Device authentication via token
- ✅ Viewer authentication & permissions
- ✅ Session management
- ✅ Real-time statistics
- ✅ Auto-cleanup on disconnect

## Installation

```bash
cd /home/ubuntu/kiosk/remote-control-poc/relay-server

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

## Configuration

Edit `.env` file:

```env
HTTP_PORT=3002          # HTTP API port
WS_PORT=3003            # WebSocket port
DB_HOST=localhost       # MariaDB host
DB_PORT=3306            # MariaDB port
DB_USER=platform_user   # Database user
DB_PASSWORD=***         # Database password
DB_NAME=platform        # Database name
```

## Usage

### Development Mode

```bash
npm run dev
```

### Production Mode

```bash
npm start
```

### With PM2 (Recommended for Production)

```bash
pm2 start server.js --name remote-control-relay
pm2 save
pm2 startup
```

## API Endpoints

### Health Check

```http
GET http://localhost:3002/health
```

Response:
```json
{
  "status": "ok",
  "uptime": 1234.56,
  "rooms": 3,
  "timestamp": 1706400000000
}
```

### Statistics

```http
GET http://localhost:3002/stats
```

Response:
```json
[
  {
    "deviceId": "123",
    "hasDevice": true,
    "viewerCount": 2,
    "stats": {
      "framesSent": 1500,
      "inputsSent": 45
    }
  }
]
```

## WebSocket Protocol

### Device Authentication (Android)

```json
{
  "type": "auth",
  "role": "device",
  "deviceId": "123",
  "token": "device_token_here"
}
```

### Viewer Authentication (CMS)

```json
{
  "type": "auth",
  "role": "viewer",
  "deviceId": "123",
  "userId": "456",
  "token": "session_token_here"
}
```

### Frame Transmission (Device → Viewers)

```json
{
  "type": "frame",
  "format": "jpeg",
  "data": "base64_encoded_image",
  "timestamp": 1706400000000
}
```

### Input Command (Viewer → Device)

```json
{
  "type": "input_command",
  "command": {
    "type": "touch",
    "x": 0.5,
    "y": 0.5,
    "normalized": true
  }
}
```

## Docker Support

### Dockerfile

```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .

EXPOSE 3002 3003

CMD ["node", "server.js"]
```

### Build & Run

```bash
docker build -t remote-control-relay .
docker run -d \
  --name remote-control-relay \
  -p 3002:3002 \
  -p 3003:3003 \
  --env-file .env \
  remote-control-relay
```

## Integration with Main Stack

Add to `/home/ubuntu/kiosk/docker-compose.prod.yml`:

```yaml
  remote-control-relay:
    build: ./remote-control-poc/relay-server
    container_name: remote-control-relay
    restart: always
    ports:
      - "3002:3002"
      - "3003:3003"
    environment:
      - DB_HOST=mariadb
      - DB_PORT=3306
      - DB_USER=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - DB_NAME=${DB_DATABASE}
    depends_on:
      - mariadb
    networks:
      - kiosk-net
```

## Monitoring

### View Logs

```bash
# With PM2
pm2 logs remote-control-relay

# With Docker
docker logs -f remote-control-relay
```

### Check Active Connections

```bash
curl http://localhost:3002/stats
```

## Troubleshooting

### Connection Refused

- Check if server is running: `pm2 status`
- Check firewall: `sudo ufw status`
- Verify ports are open: `netstat -tulpn | grep 300`

### Authentication Failed

- Verify device token in database
- Check `remotes.remote_control_enabled = true`
- Verify user permissions in `remote_permissions` table

### High Memory Usage

- Check number of active rooms: `curl http://localhost:3002/stats`
- Consider implementing frame queue limits
- Monitor with: `pm2 monit`

## Security Notes

- Use WSS (WebSocket Secure) in production
- Implement rate limiting for input commands
- Rotate device tokens periodically
- Monitor failed authentication attempts
- Enable firewall rules to restrict access

## Performance Tips

- Use binary frames instead of base64 for better performance
- Implement frame buffering/dropping when viewers lag
- Consider WebRTC for production (lower latency)
- Use Redis for session storage in multi-server setup
- Enable gzip compression for text frames

## License

MIT License - Cosmic Development Team
