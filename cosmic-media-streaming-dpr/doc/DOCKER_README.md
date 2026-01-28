# 🐳 Docker Quick Start

## Prerequisites
- Docker Desktop installed
- Git
- 4GB RAM minimum
- 20GB disk space

## 🚀 Deploy in 3 Steps

### 1. Clone & Setup
```bash
git clone <repository>
cd cosmic-media-streaming-dpr
cp .env.docker .env
```

### 2. Configure Environment
Edit `.env` file:
```env
# Change these passwords!
DB_PASSWORD=your_secure_password
DB_ROOT_PASSWORD=your_root_password
MINIO_SECRET=your_minio_password
```

### 3. Deploy
```bash
# Linux/Mac
chmod +x deploy.sh
./deploy.sh

# Windows (PowerShell)
wsl bash deploy.sh

# Or manual:
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan storage:link
```

## 📡 Access Services

| Service | URL | Credentials |
|---------|-----|-------------|
| **Application** | http://localhost:8000 | - |
| **Admin Panel** | http://localhost:8000/back-office | (from seeder) |
| **MinIO Console** | http://localhost:9001 | minioadmin / minioadmin |
| **MySQL** | localhost:3306 | cosmic_user / (from .env) |
| **Redis** | localhost:6379 | - |

## 📤 File Uploads

**All uploads now go to MinIO** automatically:
- Videos → `videos/` folder
- Images → `images/` folder
- PDFs → `pdfs/` folder
- HTML → `html/` folder

Access files:
```
http://localhost:9000/cosmic-media/{path}
```

## 🔧 Common Commands

```bash
# View logs
docker-compose logs -f app

# Restart services
docker-compose restart

# Stop all
docker-compose down

# Access app shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan queue:work
```

## 📚 Full Documentation
- [DOCKER_GUIDE.md](DOCKER_GUIDE.md) - Complete Docker guide
- [PERFORMANCE_FIXES.md](PERFORMANCE_FIXES.md) - Performance improvements
- [REDIS_QUEUE_SETUP.md](REDIS_QUEUE_SETUP.md) - Queue configuration

## 🆘 Troubleshooting

**Containers won't start:**
```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

**Can't upload files:**
- Check MinIO is running: `docker-compose ps minio`
- Access MinIO console: http://localhost:9001
- Check logs: `docker-compose logs minio`

**Database connection error:**
- Wait a few seconds for MySQL to initialize
- Check .env: `DB_HOST=mysql` (not localhost)

## ✅ Verify Installation

```bash
# Check all containers are running
docker-compose ps

# Should show:
# cosmic-media-app        Up
# cosmic-media-mysql      Up (healthy)
# cosmic-media-redis      Up
# cosmic-media-minio      Up (healthy)
# cosmic-media-queue      Up
# cosmic-media-scheduler  Up
```

Test upload at: http://localhost:8000/back-office

---

**Need help?** Check [DOCKER_GUIDE.md](DOCKER_GUIDE.md) for detailed troubleshooting.
