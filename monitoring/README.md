# Monitoring Stack - Technical Documentation

Stack monitoring untuk DPR Cosmic Media Streaming Platform menggunakan Prometheus, Grafana, cAdvisor, dan Node Exporter.

---

## 📊 Overview

Stack monitoring ini menyediakan observability lengkap untuk infrastruktur Docker, termasuk:
- **Prometheus**: Time-series database untuk metrics collection
- **Grafana**: Dashboard visualization dan alerting
- **cAdvisor**: Container metrics (CPU, memory, network, disk)
- **Node Exporter**: Host system metrics (Linux OS)

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   Grafana Dashboard                      │
│              http://localhost:3000                       │
│         (Visualization & Alerting Interface)             │
└──────────────────┬──────────────────────────────────────┘
                   │ Query
                   ▼
┌─────────────────────────────────────────────────────────┐
│                    Prometheus                            │
│              http://localhost:9090                       │
│           (Metrics Database & Scraper)                   │
└──┬───────────────┬────────────────┬─────────────────────┘
   │               │                │
   │ Scrape        │ Scrape         │ Scrape
   │ every 15s     │ every 15s      │ every 15s
   │               │                │
   ▼               ▼                ▼
┌──────────┐  ┌──────────┐  ┌──────────────────┐
│ cAdvisor │  │   Node   │  │   Prometheus     │
│  :8080   │  │ Exporter │  │   (self)         │
│          │  │  :9100   │  │   :9090          │
└──────────┘  └──────────┘  └──────────────────┘
   │               │
   │ Monitor       │ Monitor
   ▼               ▼
Docker          Linux OS
Containers      System
```

---

## 🚀 Quick Start

### 1. Start Monitoring Stack

```bash
cd /home/ubuntu/kiosk/monitoring
docker compose up -d
```

### 2. Verify Services

```bash
# Check all services running
docker compose ps

# Check logs
docker compose logs -f
```

### 3. Access Dashboards

| Service | URL | Credentials |
|---------|-----|-------------|
| **Grafana** | http://localhost:3000 | admin / admin |
| **Prometheus** | http://localhost:9090 | - |
| **cAdvisor** | http://localhost:8080 | - |
| **Node Exporter** | http://localhost:9100/metrics | - |

---

## 📁 Directory Structure

```
monitoring/
├── docker-compose.yml              # Main compose file
├── prometheus/
│   └── prometheus.yml              # Prometheus config (scrape targets)
├── grafana/
│   ├── dashboards/                 # Custom dashboard JSON files
│   └── provisioning/
│       ├── datasources/
│       │   └── datasource.yml      # Auto-provision Prometheus datasource
│       └── dashboards/
│           └── dashboards.yml      # Auto-load dashboards
└── README.md                       # This file
```

---

## 🔧 Configuration

### Prometheus Configuration

File: `prometheus/prometheus.yml`

```yaml
global:
  scrape_interval: 15s        # Scrape metrics every 15 seconds
  evaluation_interval: 15s    # Evaluate rules every 15 seconds

scrape_configs:
  - job_name: "prometheus"
    static_configs:
      - targets: ["prometheus:9090"]

  - job_name: "cadvisor"
    static_configs:
      - targets: ["cadvisor:8080"]

  - job_name: "node_exporter"
    static_configs:
      - targets: ["node-exporter:9100"]
```

**Menambah Target Monitoring Baru:**

```yaml
  - job_name: "my-service"
    static_configs:
      - targets: ["my-service:9091"]
```

Reload config tanpa restart:
```bash
curl -X POST http://localhost:9090/-/reload
```

### Grafana Configuration

**Default Credentials:**
- Username: `admin`
- Password: `admin` (akan diminta ganti saat first login)

**Datasource:** Auto-provisioned dari `grafana/provisioning/datasources/datasource.yml`

**Change Admin Password:**
```bash
docker compose exec grafana grafana-cli admin reset-admin-password NEW_PASSWORD
```

---

## 📊 Available Metrics

### cAdvisor Metrics (Container)
- `container_cpu_usage_seconds_total` - CPU usage
- `container_memory_usage_bytes` - Memory usage
- `container_network_receive_bytes_total` - Network RX
- `container_network_transmit_bytes_total` - Network TX
- `container_fs_usage_bytes` - Disk usage

### Node Exporter Metrics (System)
- `node_cpu_seconds_total` - CPU usage by core
- `node_memory_MemAvailable_bytes` - Available RAM
- `node_disk_read_bytes_total` - Disk read throughput
- `node_disk_written_bytes_total` - Disk write throughput
- `node_filesystem_avail_bytes` - Disk space available
- `node_load1`, `node_load5`, `node_load15` - System load

---

## 🎨 Creating Grafana Dashboards

### Method 1: Import Official Dashboards

1. Go to Grafana → **Dashboards** → **Import**
2. Enter Dashboard ID:
   - **Docker Monitoring**: `10619`
   - **Node Exporter Full**: `1860`
   - **cAdvisor**: `14282`
3. Click **Load** → Select **Prometheus** datasource → **Import**

### Method 2: Create Custom Dashboard

1. **Create Dashboard** → **Add Visualization**
2. Select **Prometheus** datasource
3. Enter PromQL query, contoh:

**Container Memory Usage:**
```promql
container_memory_usage_bytes{name=~"platform-.*"}
```

**CPU Usage per Container:**
```promql
rate(container_cpu_usage_seconds_total{name=~"platform-.*"}[5m]) * 100
```

**Disk Usage:**
```promql
(container_fs_usage_bytes / container_fs_limit_bytes) * 100
```

### Method 3: Save to File

1. Dashboard → **Share** → **Export** → **Save to file**
2. Copy JSON ke `grafana/dashboards/my-dashboard.json`
3. Dashboard akan auto-load saat restart

---

## 🔍 Monitoring Platform Containers

### Query Examples

**Monitor semua platform containers:**
```promql
container_memory_usage_bytes{name=~"platform-.*"}
```

**Laravel App CPU Usage:**
```promql
rate(container_cpu_usage_seconds_total{name=~"cosmic-app-.*"}[5m]) * 100
```

**MariaDB Memory:**
```promql
container_memory_usage_bytes{name="platform-mariadb-prod"}
```

**Redis Connection Stats:**
```promql
rate(container_network_receive_bytes_total{name="platform-redis-prod"}[1m])
```

**MinIO Storage:**
```promql
container_fs_usage_bytes{name="platform-minio-prod"}
```

**Nginx Traffic:**
```promql
rate(container_network_transmit_bytes_total{name="platform-nginx-prod"}[1m])
```

---

## ⚠️ Alerting Setup

### Enable Alertmanager (Optional)

1. Add to `docker-compose.yml`:

```yaml
  alertmanager:
    image: prom/alertmanager:latest
    container_name: monitoring-alertmanager
    restart: unless-stopped
    networks:
      - monitoring
    ports:
      - "9093:9093"
    volumes:
      - ./alertmanager/alertmanager.yml:/etc/alertmanager/alertmanager.yml:ro
```

2. Create `alertmanager/alertmanager.yml`:

```yaml
route:
  receiver: 'default'
  
receivers:
  - name: 'default'
    email_configs:
      - to: 'admin@example.com'
        from: 'alertmanager@example.com'
        smarthost: smtp.gmail.com:587
        auth_username: 'your-email@gmail.com'
        auth_password: 'your-app-password'
```

3. Add alert rules to `prometheus/alerts.yml`:

```yaml
groups:
  - name: container_alerts
    interval: 30s
    rules:
      - alert: HighMemoryUsage
        expr: (container_memory_usage_bytes / container_spec_memory_limit_bytes) > 0.9
        for: 2m
        labels:
          severity: warning
        annotations:
          summary: "Container {{ $labels.name }} high memory usage"
```

---

## 🛠️ Maintenance

### View Logs

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f prometheus
docker compose logs -f grafana
```

### Restart Services

```bash
# All services
docker compose restart

# Specific service
docker compose restart prometheus
```

### Update Images

```bash
docker compose pull
docker compose up -d
```

### Backup Grafana Dashboards

```bash
# Backup volume
docker run --rm -v monitoring_grafana_data:/data -v $(pwd):/backup \
  alpine tar czf /backup/grafana-backup.tar.gz -C /data .

# Restore
docker run --rm -v monitoring_grafana_data:/data -v $(pwd):/backup \
  alpine tar xzf /backup/grafana-backup.tar.gz -C /data
```

### Clean Up Old Data

```bash
# Prometheus retention (default 15 days)
docker compose exec prometheus \
  promtool tsdb create-blocks-from openmetrics /prometheus

# Or set retention in docker-compose.yml:
# command:
#   - --storage.tsdb.retention.time=30d
#   - --storage.tsdb.retention.size=50GB
```

---

## 🐛 Troubleshooting

### Prometheus tidak scrape metrics

**Check target status:**
```bash
# Via UI
open http://localhost:9090/targets

# Via API
curl http://localhost:9090/api/v1/targets
```

**Common issues:**
- Target container tidak running
- Network connectivity issue
- Wrong port configuration

**Fix:**
```bash
# Verify target reachable
docker compose exec prometheus wget -O- http://cadvisor:8080/metrics
docker compose exec prometheus wget -O- http://node-exporter:9100/metrics
```

### Grafana tidak bisa connect ke Prometheus

**Check datasource:**
```bash
curl http://localhost:3000/api/datasources
```

**Manual test:**
```bash
docker compose exec grafana wget -O- http://prometheus:9090/api/v1/query?query=up
```

### cAdvisor tidak show container stats

**Issue:** cAdvisor needs privileged mode

**Verify:**
```bash
docker compose exec cadvisor ls -la /var/lib/docker
```

**Fix:** Ensure `privileged: true` in docker-compose.yml

### High disk usage

**Check Prometheus data:**
```bash
docker compose exec prometheus du -sh /prometheus
```

**Reduce retention:**
Edit docker-compose.yml:
```yaml
command:
  - --storage.tsdb.retention.time=7d
  - --storage.tsdb.retention.size=10GB
```

---

## 📈 Performance Tuning

### Prometheus

**Scrape interval optimization:**
- Production: `15s` (current)
- High-traffic: `30s` (reduce load)
- Development: `5s` (faster feedback)

**Resource limits:**
```yaml
deploy:
  resources:
    limits:
      memory: 2G
      cpus: '1.0'
```

### Grafana

**Optimize query performance:**
- Use `$__rate_interval` instead of fixed intervals
- Limit time range untuk heavy queries
- Use caching (default 5 minutes)

---

## 🔐 Security Best Practices

1. **Change default passwords** (Grafana admin)
2. **Enable authentication** di Prometheus (nginx proxy)
3. **Firewall rules**:
   ```bash
   # Allow only localhost
   sudo ufw allow from 127.0.0.1 to any port 9090
   sudo ufw allow from 127.0.0.1 to any port 3000
   ```
4. **Use read-only volumes** (configured)
5. **Regular updates** (docker compose pull)

---

## 📚 Resources

### Official Documentation
- [Prometheus Documentation](https://prometheus.io/docs/)
- [Grafana Documentation](https://grafana.com/docs/)
- [cAdvisor GitHub](https://github.com/google/cadvisor)
- [Node Exporter Guide](https://prometheus.io/docs/guides/node-exporter/)

### Useful Dashboards
- Docker & System Monitoring: https://grafana.com/grafana/dashboards/10619
- Node Exporter Full: https://grafana.com/grafana/dashboards/1860
- Prometheus 2.0 Stats: https://grafana.com/grafana/dashboards/3662

### PromQL Tutorials
- [Prometheus Query Examples](https://prometheus.io/docs/prometheus/latest/querying/examples/)
- [PromQL Cheat Sheet](https://promlabs.com/promql-cheat-sheet/)

---

## 📞 Support & Maintenance

**Restart monitoring stack:**
```bash
cd /home/ubuntu/kiosk/monitoring
docker compose down && docker compose up -d
```

**Full cleanup (⚠️ deletes all metrics):**
```bash
docker compose down -v
docker compose up -d
```

**Health check:**
```bash
curl -f http://localhost:9090/-/healthy
curl -f http://localhost:3000/api/health
```

---

## 📝 Changelog

### 2026-01-26
- Initial monitoring stack setup
- Added Prometheus, Grafana, cAdvisor, Node Exporter
- Auto-provisioning Prometheus datasource
- Ready for platform monitoring integration

---

**Last Updated:** January 26, 2026  
**Maintained By:** DPR Infrastructure Team
