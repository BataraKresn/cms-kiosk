# Spesifikasi Server untuk Arsitektur Microservices
**Cosmic Media Streaming Platform**

**Tanggal:** 29 Januari 2026  
**Platform:** Docker Compose Microservices Architecture

---

## 📋 Executive Summary

Dokumen ini menjelaskan **spesifikasi server** yang dibutuhkan untuk menjalankan platform Cosmic Media Streaming dengan arsitektur microservices menggunakan Docker Compose.

### Platform Components:

#### **Backend Services (API & Business Logic):**
1. **Cosmic Media Streaming (Laravel)** - RESTful API + Admin Panel
   - Backend API untuk kiosk devices
   - Filament 3 Admin Panel (Web Frontend)
   - Authentication & Authorization
   - Media & Layout Management
   - Remote Control System (WebSocket relay + Android integration)

2. **Generate PDF (Node.js)** - PDF Generation Service
   - HTML to PDF conversion
   - HLS video processing
   - WebSocket real-time updates

3. **Remote Android Device (Python/Flask)** - Device Management Service
   - Device monitoring & control
   - ADB integration
   - Health checks

4. **Remote Control Relay (Node.js)** - WebSocket Relay Server
   - Real-time video streaming from Android devices
   - Input injection (touch, swipe, keyboard)
   - Session management and recording

#### **Frontend:**
- **Filament Admin Panel** (bundled in Laravel service) - Web-based admin interface
- **Android Kiosk App** (separate project) - Display client yang consume API
- **Remote Control Viewer** - Browser-based device control interface

#### **Infrastructure Services:**
- **MariaDB** - Relational database
- **Redis** - Cache & queue system
- **MinIO** - S3-compatible object storage
- **Nginx** - Reverse proxy & load balancer

### Apakah Ini Microservices?

**YA!** ✅ Platform ini adalah **true microservices architecture** karena:

1. ✅ **Service Independence** - Setiap service bisa di-deploy, scale, dan update secara terpisah
2. ✅ **Technology Diversity** - Laravel (PHP), Node.js (JavaScript), Python (Flask)
3. ✅ **Single Responsibility** - Setiap service punya tanggung jawab spesifik
4. ✅ **Distributed Deployment** - Bisa dijalankan di server yang sama ATAU terpisah
5. ✅ **API Communication** - Services berkomunikasi via HTTP/REST dan WebSocket
6. ✅ **Shared Infrastructure** - Database, cache, storage shared (common pattern)

**Microservices ≠ Must be on separate servers**
- Single server = **Monolithic Deployment of Microservices** (valid untuk small-medium scale)
- Multiple servers = **Distributed Microservices** (untuk production scale)

Kedua approach tetap disebut **microservices architecture**! Yang membedakan adalah deployment strategy, bukan arsitekturnya.

---

## 🖥️ Spesifikasi Server

### 1️⃣ **Deployment Kecil** (Development/Testing)
**Target:** 1-5 kiosk devices

#### Minimum Requirements:
- **CPU:** 4 vCPU cores
- **RAM:** 8 GB
- **Storage:** 100 GB SSD
- **Network:** 100 Mbps

#### Resource Allocation Per Service:
| Service | CPU | RAM | Storage | Notes |
|---------|-----|-----|---------|-------|
| MariaDB | 1 core | 2 GB | 20 GB | Database |
| Redis | 0.5 core | 512 MB | 5 GB | Cache & Queue |
| MinIO | 0.5 core | 1 GB | 50 GB | Object Storage |
| Nginx | 0.5 core | 512 MB | 1 GB | Load Balancer |
| Cosmic App (x1) | 1 core | 2 GB | 5 GB | Laravel Main App |
| Queue Workers (x2) | 0.5 core | 1 GB | 2 GB | Background Jobs |
| Generate PDF | 1 core | 1 GB | 5 GB | PDF Service |
| Remote Android | 0.5 core | 512 MB | 2 GB | Device Management |
| Remote Relay | 0.5 core | 512 MB | 2 GB | WebSocket Relay |

**Total:** 6 cores / 9 GB RAM / 95 GB Storage

**Estimated Cost:** $20-40/month
- DigitalOcean Droplet Basic
- Vultr Cloud Compute
- Linode Shared

---

### 2️⃣ **Deployment Menengah** (Production)
**Target:** 5-25 kiosk devices

#### Recommended Requirements:
- **CPU:** 8 vCPU cores
- **RAM:** 16 GB
- **Storage:** 250 GB SSD
- **Network:** 500 Mbps

#### Resource Allocation Per Service:
| Service | CPU | RAM | Storage | Replicas | Notes |
|---------|-----|-----|---------|----------|-------|
| MariaDB | 2 cores | 4 GB | 100 GB | 1 | Primary database |
| Redis | 1 core | 2 GB | 10 GB | 1 | Cache with persistence |
| MinIO | 1 core | 2 GB | 100 GB | 1 | S3-compatible storage |
| Nginx | 1 core | 1 GB | 2 GB | 1 | Reverse proxy + LB |
| Cosmic App (x3) | 3 cores | 6 GB | 15 GB | 3 | Load balanced |
| Queue Workers (x3) | 1.5 cores | 3 GB | 6 GB | 3 | Parallel processing |
| Generate PDF (x2) | 2 cores | 2 GB | 10 GB | 2 | PDF generation |
| Remote Android (x2) | 1 core | 1 GB | 4 GB | 2 | Device monitoring |
| Remote Relay (x2) | 1 core | 1 GB | 4 GB | 2 | WebSocket handling |

**Total:** 12.5 cores / 22 GB RAM / 251 GB Storage

**Estimated Cost:** $80-120/month
- DigitalOcean General Purpose
- Hetzner Cloud CX41
- AWS EC2 t3.xlarge (with reserved instance)

---

### 3️⃣ **Deployment Besar** (High Traffic Production)
**Target:** 25-100 kiosk devices

#### High-Performance Requirements:
- **CPU:** 16 vCPU cores
- **RAM:** 32 GB
- **Storage:** 500 GB SSD (or 1 TB for media-heavy)
- **Network:** 1 Gbps

#### Resource Allocation Per Service:
| Service | CPU | RAM | Storage | Replicas | Notes |
|---------|-----|-----|---------|----------|-------|
| MariaDB | 4 cores | 8 GB | 250 GB | 1 | Optimized with read replicas |
| Redis | 2 cores | 4 GB | 20 GB | 1 | High-performance cache |
| MinIO | 2 cores | 4 GB | 300 GB | 1 | Distributed object storage |
| Nginx | 2 cores | 2 GB | 5 GB | 1 | High-throughput proxy |
| Cosmic App (x5) | 5 cores | 10 GB | 25 GB | 5 | Horizontal scaling |
| Queue Workers (x5) | 2.5 cores | 5 GB | 10 GB | 5 | Heavy background processing |
| Generate PDF (x3) | 3 cores | 4 GB | 15 GB | 3 | Concurrent PDF generation |
| Remote Android (x3) | 1.5 cores | 2 GB | 6 GB | 3 | Multiple device pools |
| Remote Relay (x3) | 2 cores | 3 GB | 10 GB | 3 | High concurrency WebSocket |

**Total:** 24 cores / 42 GB RAM / 641 GB Storage

**Estimated Cost:** $200-350/month
- Hetzner Cloud CCX32
- DigitalOcean CPU-Optimized
- AWS EC2 c6i.4xlarge

---

### 4️⃣ **Deployment Enterprise** (Multi-Region/High Availability)
**Target:** 100+ kiosk devices, multi-location

#### Enterprise Requirements:
- **CPU:** 32+ vCPU cores (atau multi-server cluster)
- **RAM:** 64 GB+
- **Storage:** 1 TB+ SSD (NVMe recommended)
- **Network:** 10 Gbps
- **High Availability:** Multi-server dengan load balancing

#### Multi-Server Architecture:

**Server 1: Database & Cache Cluster**
- CPU: 8 cores
- RAM: 16 GB
- Storage: 500 GB
- Services: MariaDB Primary, Redis Primary

**Server 2: Application Cluster**
- CPU: 16 cores
- RAM: 32 GB
- Storage: 300 GB
- Services: Cosmic App (x8), Queue Workers (x8)

**Server 3: Services Cluster**
- CPU: 8 cores
- RAM: 16 GB
- Storage: 300 GB
- Services: Generate PDF (x4), Remote Android (x4), Remote Relay (x4), Nginx LB

**Server 4: Storage & Backup**
- CPU: 4 cores
- RAM: 8 GB
- Storage: 2 TB
- Services: MinIO Cluster, Backup Services

**Total Cluster:** 36 cores / 72 GB RAM / 3.1 TB Storage

**Estimated Cost:** $500-1,000/month (untuk bare metal atau cloud)

**Catatan:** Pada scale ini, pertimbangkan migrasi ke Kubernetes untuk:
- Automatic scaling
- Self-healing
- Multi-region deployment
- Advanced load balancing

---

## 📊 Resource Breakdown by Service

### **Cosmic Media Streaming (Laravel App)**

| Scale | Replicas | CPU/Instance | RAM/Instance | Total CPU | Total RAM |
|-------|----------|--------------|--------------|-----------|-----------|
| Small | 1 | 1 core | 2 GB | 1 core | 2 GB |
| Medium | 3 | 1 core | 2 GB | 3 cores | 6 GB |
| Large | 5 | 1 core | 2 GB | 5 cores | 10 GB |
| Enterprise | 8-10 | 1-2 cores | 2-3 GB | 12-20 cores | 20-30 GB |

**Notes:**
- PHP-FPM dengan 75 max children per instance
- OPcache 256MB per instance
- Composer optimized dengan APCu

---

### **Remote Control Relay (Node.js WebSocket)**

| Scale | Replicas | CPU/Instance | RAM/Instance | Total CPU | Total RAM |
|-------|----------|--------------|--------------|-----------|-----------|
| Small | 1 | 0.5 core | 512 MB | 0.5 core | 512 MB |
| Medium | 2 | 0.5 core | 512 MB | 1 core | 1 GB |
| Large | 3 | 1 core | 1 GB | 3 cores | 3 GB |
| Enterprise | 4-6 | 1 core | 1-2 GB | 4-6 cores | 6-12 GB |

**Notes:**
- WebSocket connections: 100-500 concurrent per instance
- Video frame relay: 30 FPS per device
- Session management and database logging
- Lightweight compared to application services

---

## 🌐 Network Requirements

### Bandwidth Estimation

| Kiosk Count | Concurrent Streams | Avg Bandwidth | Peak Bandwidth | Monthly Transfer |
|-------------|-------------------|---------------|----------------|------------------|
| 1-5 | 2-5 | 10 Mbps | 25 Mbps | 500 GB |
| 5-25 | 5-15 | 50 Mbps | 150 Mbps | 2 TB |
| 25-100 | 15-50 | 200 Mbps | 500 Mbps | 10 TB |
| 100+ | 50+ | 500 Mbps+ | 1+ Gbps | 25+ TB |

**Notes:**
- Video streaming: ~2-5 Mbps per stream (1080p)
- Remote control video: ~1-2 Mbps per device
- API requests: ~100 KB per request
- WebSocket: ~10-50 KB/s per connection
- File uploads: Burst bandwidth required

---

## 🔄 Inter-Service Communication

### Service Responsibilities Matrix

| Service | Primary Role | Technologies | Endpoints | Dependencies |
|---------|-------------|--------------|-----------|--------------|
| **Cosmic App** | Backend API + Admin UI | Laravel 10, PHP 8.2, Filament 3 | `/api/*`, `/back-office/*` | MariaDB, Redis, MinIO |
| **Queue Worker** | Background jobs | Laravel Queue, FFmpeg | N/A (worker) | MariaDB, Redis, MinIO |
| **Generate PDF** | PDF generation | Node.js, Puppeteer, WebSocket | `/generate`, `/ws` | MariaDB, MinIO |
| **Remote Android** | Device management | Python 3, Flask, ADB | `/device/*`, `/control/*` | MariaDB |
| **Remote Relay** | WebSocket relay | Node.js, ws, Express | `/remote-control-ws`, `/stats` | MariaDB |
| **Nginx** | Load balancer + proxy | Nginx 1.24 | All (reverse proxy) | All app services |
| **MariaDB** | Primary database | MariaDB 10.11 | 3306 | None (data layer) |
| **Redis** | Cache + queue | Redis 7 | 6379 | None (data layer) |
| **MinIO** | Object storage | MinIO (S3-compatible) | 9000, 9001 | None (data layer) |

---

## 💾 Storage Requirements Detail

### Database Storage (MariaDB)

| Data Type | Size per Kiosk | 25 Kiosks | 100 Kiosks | Notes |
|-----------|----------------|-----------|------------|-------|
| User data | 10 MB | 250 MB | 1 GB | Users, roles, permissions |
| Media metadata | 50 MB | 1.25 GB | 5 GB | Video/image/HTML info |
| Playlists | 20 MB | 500 MB | 2 GB | Schedules & assignments |
| Device data | 5 MB | 125 MB | 500 MB | Kiosk registrations |
| Remote sessions | 10 MB | 250 MB | 1 GB | Remote control logs |
| Logs | 100 MB | 2.5 GB | 10 GB | Activity logs (30 days) |
| **Total** | **195 MB** | **4.9 GB** | **19.5 GB** | + 20% growth buffer |

**Recommended Database Storage:**
- Small (1-5 kiosks): 20 GB
- Medium (5-25 kiosks): 100 GB
- Large (25-100 kiosks): 250 GB
- Enterprise (100+ kiosks): 500 GB+

---

### Object Storage (MinIO)

| Content Type | Size per Kiosk | 25 Kiosks | 100 Kiosks | Notes |
|--------------|----------------|-----------|------------|-------|
| Videos | 2 GB | 50 GB | 200 GB | Original + HLS segments |
| Images | 500 MB | 12.5 GB | 50 GB | Original + thumbnails |
| PDFs | 100 MB | 2.5 GB | 10 GB | Generated reports |
| HTML content | 50 MB | 1.25 GB | 5 GB | Webview content |
| Remote recordings | 500 MB | 12.5 GB | 50 GB | Video recordings |
| Backups | 500 MB | 12.5 GB | 50 GB | Database backups |
| **Total** | **3.65 GB** | **91.3 GB** | **365 GB** | + 50% growth buffer |

**Recommended Object Storage:**
- Small (1-5 kiosks): 50 GB
- Medium (5-25 kiosks): 150 GB (140 GB + buffer)
- Large (25-100 kiosks): 550 GB (550 GB + buffer)
- Enterprise (100+ kiosks): 1-2 TB

---

## 🔐 Security Best Practices

### Network Security

**Firewall Configuration:**
```bash
# Public Access (Load Balancer)
Allow: 0.0.0.0/0:443 -> nginx:443 (HTTPS)
Allow: 0.0.0.0/0:80 -> nginx:80 (HTTP redirect)

# Internal Services (Private Network)
Allow: nginx -> cosmic-app:8000
Allow: nginx -> remote-relay:3003 (WebSocket)
Allow: cosmic-app -> mariadb:3306
Allow: cosmic-app -> redis:6379
Allow: cosmic-app -> minio:9000

# Block Direct Access
Deny: 0.0.0.0/0 -> mariadb:3306
Deny: 0.0.0.0/0 -> redis:6379
Deny: 0.0.0.0/0 -> minio:9000
```

### WebSocket Security

**Remote Control Authentication:**
- Token-based authentication (64-char random token)
- Device identifier validation
- IP whitelisting (Tailscale VPN recommended)
- Session expiration (24 hours)
- Admin-only access to remote control viewer

---

## 📈 Performance Benchmarks

### Expected Performance per Configuration:

| Configuration | Concurrent Users | Requests/sec | Response Time | Kiosk Support | Remote Sessions |
|---------------|------------------|--------------|---------------|---------------|-----------------|
| Small | 10-50 | 100-500 | <200ms | 1-5 | 1-2 |
| Medium | 50-200 | 500-2000 | <150ms | 5-25 | 5-10 |
| Large | 200-1000 | 2000-5000 | <100ms | 25-100 | 10-25 |
| Enterprise | 1000+ | 5000+ | <50ms | 100+ | 25+ |

**Metrics Include:**
- API response time
- Page load time
- Database query time
- Cache hit ratio (>90%)
- WebSocket latency (<100ms)

---

## 💰 Cost Analysis

### Monthly Server Costs by Provider

#### **Small Deployment (8GB RAM, 4 vCPU, 100GB SSD)**

| Provider | Instance Type | Monthly Cost | Notes |
|----------|---------------|--------------|-------|
| DigitalOcean | Basic Droplet | $48 | Good support, easy to use |
| Vultr | Cloud Compute | $36 | Cheaper, similar performance |
| Hetzner | CX31 | €12.90 (~$14) | Best value, EU only |
| Linode | Shared 8GB | $48 | Reliable, good network |
| AWS EC2 | t3.large (reserved) | $54 | 1-year commitment |
| OVH | VPS Value | €20 (~$22) | Good EU coverage |

**Recommendation:** Hetzner untuk EU, Vultr untuk global

---

#### **Medium Deployment (16GB RAM, 8 vCPU, 250GB SSD)**

| Provider | Instance Type | Monthly Cost | Notes |
|----------|---------------|--------------|-------|
| DigitalOcean | General Purpose | $96 | Managed databases available |
| Vultr | High Frequency | $72 | NVMe SSD, good performance |
| Hetzner | CX41 | €24.90 (~$27) | Excellent value |
| Linode | Dedicated 16GB | $96 | Dedicated CPU |
| AWS EC2 | t3.xlarge (reserved) | $109 | 1-year commitment |
| OVH | VPS Comfort | €40 (~$44) | Good performance |

**Recommendation:** Hetzner CX41 (best value) atau Vultr (global)

---

## ✅ Decision Matrix

### Pilih Konfigurasi Berdasarkan Kebutuhan:

| Kriteria | Small | Medium | Large | Enterprise |
|----------|-------|--------|-------|------------|
| **Kiosk Count** | 1-5 | 5-25 | 25-100 | 100+ |
| **Concurrent Users** | <50 | 50-200 | 200-1000 | 1000+ |
| **Remote Sessions** | 1-2 | 5-10 | 10-25 | 25+ |
| **Budget/Month** | $30-55 | $130-240 | $550-1000 | $1000+ |
| **Deployment** | Single Server | Single Server | Hybrid (3 servers) | Distributed (6+ servers) |
| **Uptime SLA** | 95% | 99% | 99.5% | 99.9% |
| **Support Level** | Community | Email | Priority | 24/7 |
| **Backup Strategy** | Daily | Hourly | Continuous | Multi-region |
| **Scaling Method** | Vertical | Horizontal | Multi-server | K8s Cluster |
| **HA (Redundancy)** | No | No | Partial | Full |
| **Replication** | No | Optional | Yes (DB) | Yes (All) |

---

## 🚀 Quick Start Guide

### Single Server Deployment (Recommended for Start)

```bash
# Server requirements: 16GB RAM, 8 vCPU, 250GB SSD
# Provider: Hetzner CX41 (~$27/month) or similar

# 1. SSH to server
ssh root@your-server-ip

# 2. Clone repository
git clone <your-repo-url> /home/ubuntu/kiosk
cd /home/ubuntu/kiosk

# 3. Configure environment
cp .env.example .env
nano .env  # Edit database, redis, minio credentials

# 4. Deploy all services
./deploy-prod.sh

# 5. Verify deployment
docker ps  # Should show all services running

# 6. Access admin panel
# https://your-domain.com/back-office
```

**Result:** All microservices running on single server
**Time:** 30 minutes
**Cost:** $27-100/month

---

## 📚 Related Documentation

- [DEPLOYMENT_GUIDE.md](../DEPLOYMENT_GUIDE.md) - Production deployment steps
- [QUICK_REFERENCE.md](QUICK_REFERENCE.md) - Quick command reference
- [DATABASE_BACKUP_GUIDE.md](DATABASE_BACKUP_GUIDE.md) - Backup procedures

---

## ❓ FAQ

### Q: Apakah platform ini microservices atau monolith?
**A:** **Microservices architecture** 100%! Services terpisah dengan tanggung jawab independen.

### Q: Apakah harus deploy di server terpisah untuk disebut microservices?
**A:** **TIDAK!** Microservices adalah tentang **architecture pattern**, bukan deployment topology.

### Q: Kapan harus pindah dari single server ke multiple servers?
**A:** Pertimbangkan distributed deployment jika:
- Traffic > 1,000 concurrent users
- Resource contention (services rebutan CPU/RAM)
- Butuh high availability (99.9% uptime)
- Remote control sessions > 25 concurrent

### Q: Bagaimana performa Remote Control System?
**A:** 
- Latency: < 100ms (same datacenter)
- FPS: 30 FPS stable
- Concurrent sessions: 1-2 (small), 5-10 (medium), 25+ (enterprise)
- Bandwidth per session: 1-2 Mbps

---

**Document Version:** 3.0  
**Last Updated:** 29 Januari 2026  
**Prepared by:** Development Team  
**Review Status:** ✅ Complete & Production Ready

**Next Review Date:** 29 April 2026 (3 months)
