# Nginx Reverse Proxy Configuration

## Overview
High-performance Nginx configuration untuk Cosmic Media Streaming Platform dengan support untuk large file uploads dan optimasi performance.

## Location
**Config File:** `/home/ubuntu/kiosk/cosmic-media-streaming-dpr/nginx.conf`

## Key Features

### 1. **Large File Upload Support**
- **Maximum Upload Size:** 2048 MB (2 GB)
- **Upload Timeout:** 600 seconds (10 minutes)
- **Buffer Size:** 10 MB
- **Request Buffering:** Disabled untuk upload yang lebih efisien

### 2. **Custom Ports (Non-Standard)**
```
HTTP:  Port 8080 (bukan 80)
HTTPS: Port 8443 (bukan 443) - ready untuk SSL
```

**Alasan custom port:**
- ✅ Menghindari konflik dengan service lain
- ✅ Lebih aman (tidak standar port)
- ✅ Flexible untuk multiple deployments

### 3. **Performance Optimizations**

#### Worker Configuration
```nginx
worker_processes auto;           # Auto-detect CPU cores
worker_connections 4096;         # 4K concurrent connections per worker
worker_rlimit_nofile 65535;     # Max open files
```

#### Connection Optimization
```nginx
keepalive_timeout 65;
keepalive_requests 1000;
tcp_nopush on;
tcp_nodelay on;
sendfile on;
```

#### Proxy Settings
```nginx
proxy_buffering off;              # Disable buffering untuk streaming
proxy_request_buffering off;      # Disable untuk large uploads
proxy_http_version 1.1;           # HTTP/1.1 untuk keepalive
```

### 4. **Rate Limiting**
```nginx
API Endpoints:    100 req/s (burst 50)
Upload Endpoints: 10 req/s (burst 5)
Connections:      50 concurrent per IP
```

### 5. **Upstream Load Balancing**
```nginx
upstream cosmic_app_backend {
    least_conn;                    # Load balancing method
    server cosmic-app:8000;
    keepalive 32;                  # Persistent connections
}
```

### 6. **Compression**
```nginx
gzip on;
gzip_comp_level 6;
gzip_types: text/*, application/json, application/javascript
```

## Routing Configuration

### Main Application (Cosmic Media)
```
Location: /
Upstream: cosmic-app:8000
Limits:   100 req/s, 50 concurrent connections
Features: WebSocket support, keepalive
```

### Upload Endpoints
```
Location: /api/upload, /upload, /storage
Max Size: 2048 MB (2 GB)
Timeout:  600 seconds
Limits:   10 req/s upload rate
Features: No buffering, optimized for large files
```

### PDF Generation Service
```
Location: /pdf/
Upstream: generate-pdf:3333
Max Size: 100 MB
```

### Remote Android Service
```
Location: /android/
Upstream: remote-android:3001
Features: WebSocket support
```

### Health Check
```
Location: /health
Response: 200 "healthy"
No logging
```

## Upload Performance Limits

| Scenario | Max Size | Timeout | Rate Limit |
|----------|----------|---------|------------|
| Regular API | 2048 MB | 300s | 100 req/s |
| Upload Endpoint | 2048 MB | 600s | 10 req/s |
| PDF Generation | 100 MB | 300s | 100 req/s |
| Remote Android | Default | 300s | 100 req/s |

## Security Features

### Headers
```nginx
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: no-referrer-when-downgrade
```

### Protection
- ✅ Hide Nginx version (`server_tokens off`)
- ✅ Block hidden files (`.htaccess`, `.git`, etc)
- ✅ Rate limiting untuk prevent abuse
- ✅ Connection limits per IP

## SSL/HTTPS Configuration (Ready but Commented)

Untuk enable HTTPS di port 8443:

1. **Generate SSL Certificate:**
```bash
# Self-signed (untuk testing)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout data-kiosk/nginx/ssl/key.pem \
  -out data-kiosk/nginx/ssl/cert.pem

# Atau gunakan Let's Encrypt (untuk production)
certbot certonly --standalone -d yourdomain.com
```

2. **Uncomment HTTPS server block** di nginx.conf (line ~170)

3. **Restart nginx:**
```bash
docker compose -f docker-compose.prod.yml restart nginx
```

## Testing Upload

### Test 100MB Upload
```bash
# Generate test file
dd if=/dev/zero of=test_100mb.bin bs=1M count=100

# Upload via curl
curl -X POST http://localhost:8080/api/upload \
  -F "file=@test_100mb.bin" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test 500MB Upload
```bash
# Generate test file
dd if=/dev/zero of=test_500mb.bin bs=1M count=500

# Upload via curl with progress
curl -X POST http://localhost:8080/api/upload \
  -F "file=@test_500mb.bin" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --progress-bar
```

### Test 1GB Upload
```bash
dd if=/dev/zero of=test_1gb.bin bs=1M count=1024

curl -X POST http://localhost:8080/api/upload \
  -F "file=@test_1gb.bin" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --max-time 600
```

## Monitoring

### Real-time Logs
```bash
# Access logs
docker exec platform-nginx-prod tail -f /var/log/nginx/access.log

# Error logs
docker exec platform-nginx-prod tail -f /var/log/nginx/error.log

# Both logs
docker logs -f platform-nginx-prod
```

### Check Nginx Status
```bash
# Verify config syntax
docker exec platform-nginx-prod nginx -t

# Reload config (tanpa downtime)
docker exec platform-nginx-prod nginx -s reload

# Check version
docker exec platform-nginx-prod nginx -v
```

## Performance Tuning

### OS-Level (untuk VPS/Dedicated Server)
```bash
# Increase file descriptors
ulimit -n 65535

# Kernel parameters (add to /etc/sysctl.conf)
net.core.somaxconn = 65535
net.ipv4.tcp_max_syn_backlog = 65535
net.ipv4.ip_local_port_range = 1024 65535
net.ipv4.tcp_tw_reuse = 1
```

### Docker Resource Limits (optional)
```yaml
nginx:
  deploy:
    resources:
      limits:
        cpus: '2.0'
        memory: 2G
      reservations:
        cpus: '1.0'
        memory: 512M
```

## Troubleshooting

### Issue: 413 Request Entity Too Large
**Solution:** Sudah handled dengan `client_max_body_size 2048M`

### Issue: 504 Gateway Timeout
**Solution:** Timeout sudah diperpanjang ke 600s untuk upload

### Issue: Connection Reset
**Solution:** Buffering disabled, gunakan streaming

### Issue: Slow Upload
**Possible Causes:**
1. Network bandwidth limit
2. Disk I/O bottleneck
3. Check dengan: `docker stats platform-nginx-prod`

## Access URLs (Production)

```
Main App:       http://localhost:8080
              or http://YOUR_IP:8080

PDF Service:    http://localhost:8080/pdf/
Android Remote: http://localhost:8080/android/
Health Check:   http://localhost:8080/health

HTTPS (when configured):
                https://localhost:8443
```

## Best Practices

1. ✅ **Always test large uploads** sebelum production
2. ✅ **Monitor disk space** untuk cache dan logs
3. ✅ **Rotate logs** dengan logrotate
4. ✅ **Use HTTPS** untuk production
5. ✅ **Adjust rate limits** sesuai kebutuhan
6. ✅ **Monitor resource usage** dengan `docker stats`

## Log Rotation

Create `/etc/logrotate.d/nginx-docker`:
```bash
/home/ubuntu/kiosk/data-kiosk/nginx/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    notifempty
    missingok
    postrotate
        docker exec platform-nginx-prod nginx -s reopen
    endscript
}
```

## Summary

✅ **Upload Limit:** 2 GB (2048 MB)
✅ **Timeout:** 10 minutes (600 seconds)
✅ **Custom Ports:** 8080 (HTTP), 8443 (HTTPS)
✅ **Performance:** Optimized untuk high traffic
✅ **Security:** Headers, rate limiting, connection limits
✅ **Ready for SSL:** Tinggal uncomment dan configure
✅ **Location:** cosmic-media-streaming-dpr/nginx.conf

**Nginx sudah optimal untuk upload > 500MB dengan best performance!** 🚀
