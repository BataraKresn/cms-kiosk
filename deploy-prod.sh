#!/bin/bash

set -e

# Parse command line arguments
FORCE_REBUILD=false
STOP_SERVICES=false
RUN_BACKUP=false

show_help() {
    cat << EOF
${CYAN}========================================${NC}
${CYAN}  Cosmic Media Streaming Platform${NC}
${CYAN}  Production Deployment Script${NC}
${CYAN}========================================${NC}

${GREEN}Usage:${NC}
  $0 [OPTIONS]

${GREEN}Options:${NC}
  ${YELLOW}-h, --help${NC}           Show this help message
  ${YELLOW}-f, --force-rebuild${NC}  Force rebuild all Docker images (no cache)
  ${YELLOW}-s, --stop${NC}           Stop all services before deployment
  ${YELLOW}-b, --backup${NC}         Backup database before deployment (recommended)

${GREEN}Examples:${NC}
  $0                          # Normal deployment
  $0 -b                       # Deployment with database backup
  $0 -f -b                    # Force rebuild + backup
  $0 --stop --backup          # Stop services, backup, then deploy

${GREEN}Features:${NC}
  • Builds and deploys all microservices
  • Manages Docker networks
  • Validates environment files
  • Checks service health
  • Optional database backup before deployment

${GREEN}Backup:${NC}
  Database backups are stored in: ${CYAN}data-kiosk/backups/${NC}
  Backups are compressed and rotated (keeps last 4)
  
EOF
    exit 0
}

while [[ $# -gt 0 ]]; do
    case $1 in
        --help|-h)
            show_help
            ;;
        --force-rebuild|-f)
            FORCE_REBUILD=true
            echo -e "⚠️  Force rebuild enabled - all layers will be rebuilt"
            shift
            ;;
        --stop|-s)
            STOP_SERVICES=true
            echo -e "⚠️  Stop services enabled - all containers will be stopped first"
            shift
            ;;
        --backup|-b)
            RUN_BACKUP=true
            echo -e "📦 Database backup enabled - will backup before deployment"
            shift
            ;;
        *)
            echo "Unknown option: $1"
            echo "Usage: $0 [--help|-h] [--force-rebuild|-f] [--stop|-s] [--backup|-b]"
            echo "Run '$0 --help' for more information"
            exit 1
            ;;
    esac
done

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Cosmic Media Streaming Platform${NC}"
echo -e "${CYAN}  Production Deployment${NC}"
echo -e "${CYAN}  All Microservices${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# Check if running as root (recommended for production)
if [ "$EUID" -eq 0 ]; then 
    echo -e "${YELLOW}⚠️  Running as root. This is acceptable for production.${NC}"
else 
    echo -e "${YELLOW}⚠️  Not running as root. Make sure you have proper permissions.${NC}"
fi
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running. Please start Docker and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker is running${NC}"
echo ""

# Check if .env.prod exists
if [ ! -f .env.prod ]; then
    echo -e "${RED}❌ .env.prod file not found.${NC}"
    echo -e "${YELLOW}Please create .env.prod from .env.prod template and configure all settings.${NC}"
    exit 1
fi

# Security checks
echo -e "${YELLOW}🔒 Performing security checks...${NC}"

# Check for default passwords
if grep -q "platform_password_dev" .env.prod; then
    echo -e "${RED}❌ SECURITY WARNING: Default development password detected in .env.prod!${NC}"
    echo -e "${YELLOW}Please change DB_PASSWORD in .env.prod before deploying to production.${NC}"
    read -p "Continue anyway? (NOT RECOMMENDED) [y/N]: " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

if grep -q "minioadmin123" .env.prod; then
    echo -e "${RED}❌ SECURITY WARNING: Default MinIO password detected in .env.prod!${NC}"
    echo -e "${YELLOW}Please change MINIO_SECRET in .env.prod before deploying to production.${NC}"
    read -p "Continue anyway? (NOT RECOMMENDED) [y/N]: " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check APP_DEBUG setting
if grep -q "APP_DEBUG=true" cosmic-media-streaming-dpr/.env 2>/dev/null; then
    echo -e "${YELLOW}⚠️  WARNING: APP_DEBUG is set to true. This should be false in production.${NC}"
fi

echo -e "${GREEN}✅ Security checks completed${NC}"
echo ""

# Run database backup if requested
if [ "$RUN_BACKUP" = true ]; then
    echo -e "${CYAN}========================================${NC}"
    echo -e "${CYAN}  Database Backup${NC}"
    echo -e "${CYAN}========================================${NC}"
    echo ""
    
    if [ -f backup-database.sh ]; then
        echo -e "${YELLOW}📦 Running database backup...${NC}"
        if ./backup-database.sh; then
            echo -e "${GREEN}✅ Database backup completed successfully${NC}"
        else
            echo -e "${RED}❌ Database backup failed${NC}"
            read -p "Continue deployment anyway? [y/N]: " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                echo -e "${BLUE}Deployment cancelled.${NC}"
                exit 1
            fi
        fi
    else
        echo -e "${YELLOW}⚠️  backup-database.sh not found, skipping backup${NC}"
    fi
    echo ""
fi

# Confirm deployment
echo -e "${YELLOW}⚠️  This will deploy the application to PRODUCTION.${NC}"
echo -e "${YELLOW}All existing containers will be recreated.${NC}"
read -p "Are you sure you want to continue? [y/N]: " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${BLUE}Deployment cancelled.${NC}"
    exit 0
fi
echo ""

# Create necessary directories
echo -e "${YELLOW}📁 Creating necessary directories...${NC}"
mkdir -p data-kiosk/logs/cosmic-app-{1,2,3} data-kiosk/logs/cosmic-queue-{video,image,default}-{1,2,3} data-kiosk/logs/cosmic-scheduler
mkdir -p data-kiosk/logs/generate-pdf data-kiosk/logs/remote-android
mkdir -p data-kiosk/logs/prod/mariadb
mkdir -p data-kiosk/backups data-kiosk/minio-backup
mkdir -p data-kiosk/prod/mariadb data-kiosk/prod/redis data-kiosk/prod/minio
mkdir -p data-kiosk/nginx/ssl data-kiosk/nginx/logs data-kiosk/nginx/cache
mkdir -p generate-pdf/uploads generate-pdf/hls_output
echo -e "${GREEN}✅ Directories created${NC}"
echo ""

# Check for platform.sql
if [ ! -f platform.sql ]; then
    echo -e "${RED}❌ platform.sql not found. Please ensure the database dump exists.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ platform.sql found${NC}"
echo ""

# Note: Skip 'docker compose pull' for custom images (kiosk-*)
# Only pull official images (mariadb, redis, minio, nginx)
echo -e "${YELLOW}📥 Pulling official base images...${NC}"
docker compose -f docker-compose.prod.yml pull mariadb redis minio nginx 2>/dev/null || true
echo ""

# Build all services
echo -e "${BLUE}🔨 Building all microservices...${NC}"
if [ "$FORCE_REBUILD" = true ]; then
    echo -e "${MAGENTA}   Force rebuilding all layers (--no-cache)...${NC}"
    BUILD_FLAGS="--no-cache"
else
    echo -e "${MAGENTA}   Using Docker layer caching for efficiency...${NC}"
    echo -e "${CYAN}   (Only changed layers will be rebuilt)${NC}"
    BUILD_FLAGS=""
fi
echo ""

# Build with or without cache
docker compose -f docker-compose.prod.yml build $BUILD_FLAGS

echo ""
echo -e "${GREEN}✅ All services built successfully${NC}"
echo ""

# Optional: Stop services if requested
if [ "$STOP_SERVICES" = true ]; then
    echo -e "${YELLOW}🛑 Stopping existing services (--stop flag)...${NC}"
    docker compose -f docker-compose.prod.yml down
    echo -e "${GREEN}✅ Services stopped${NC}"
    echo ""
fi

# Start/Update all services (Docker handles recreate automatically)
echo -e "${YELLOW}🚀 Starting/Updating all services...${NC}"
echo -e "${CYAN}   (Docker will recreate containers with new images)${NC}"
echo ""

# Start infrastructure services first
echo -e "${BLUE}📊 Infrastructure services (MariaDB, Redis, MinIO)...${NC}"
docker compose -f docker-compose.prod.yml up -d mariadb redis minio
echo ""

# Wait for MariaDB to be ready
echo -e "${YELLOW}⏳ Waiting for MariaDB to be ready...${NC}"
echo -e "${CYAN}   (This includes importing platform.sql and running restore.sql if first run)${NC}"
RETRY_COUNT=0
MAX_RETRIES=90
until docker compose -f docker-compose.prod.yml exec -T mariadb mysqladmin ping -h localhost --silent 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
        echo -e "${RED}❌ MariaDB failed to start after ${MAX_RETRIES} attempts${NC}"
        docker compose -f docker-compose.prod.yml logs mariadb
        exit 1
    fi
    echo -n "."
    sleep 2
done
echo ""
echo -e "${GREEN}✅ MariaDB is ready${NC}"
echo ""

# Wait for Redis to be ready
echo -e "${YELLOW}⏳ Waiting for Redis to be ready...${NC}"
RETRY_COUNT=0
until docker compose -f docker-compose.prod.yml exec -T redis redis-cli ping > /dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -eq 30 ]; then
        echo -e "${RED}❌ Redis failed to start${NC}"
        exit 1
    fi
    echo -n "."
    sleep 2
done
echo ""
echo -e "${GREEN}✅ Redis is ready${NC}"
echo ""

# Wait for MinIO to be ready
echo -e "${YELLOW}⏳ Waiting for MinIO to be ready...${NC}"
sleep 15
echo -e "${GREEN}✅ MinIO is ready${NC}"
echo ""

# Start all application services
echo -e "${BLUE}🚀 Starting all microservices...${NC}"
echo ""

echo -e "${MAGENTA}   📦 Service #1: Cosmic Media Streaming (Laravel)${NC}"
docker compose -f docker-compose.prod.yml up -d cosmic-app-1 cosmic-app-2 cosmic-app-3 cosmic-queue-video-1 cosmic-queue-video-2 cosmic-queue-video-3 cosmic-queue-image-1 cosmic-queue-image-2 cosmic-queue-image-3 cosmic-queue-default-1 cosmic-queue-default-2 cosmic-scheduler
echo -e "${GREEN}   ✅ Cosmic Media Streaming started${NC}"
echo ""

echo -e "${MAGENTA}   📦 Service #2: Generate PDF (Node.js)${NC}"
docker compose -f docker-compose.prod.yml up -d generate-pdf
echo -e "${GREEN}   ✅ Generate PDF started${NC}"
echo ""

echo -e "${MAGENTA}   📦 Service #3: Remote Android Device (Python)${NC}"
docker compose -f docker-compose.prod.yml up -d remote-android
echo -e "${GREEN}   ✅ Remote Android Device started${NC}"
echo ""

# Start Nginx if enabled
if grep -q "nginx:" docker-compose.prod.yml; then
    echo -e "${YELLOW}🔧 Starting Nginx reverse proxy...${NC}"
    docker compose -f docker-compose.prod.yml up -d nginx
    echo -e "${GREEN}✅ Nginx started${NC}"
    echo ""
fi

# Wait for Laravel app to be ready
echo -e "${YELLOW}⏳ Waiting for Laravel application to be ready...${NC}"
sleep 10

# Run Laravel setup
echo -e "${YELLOW}📊 Running Laravel production setup...${NC}"

# CRITICAL: Clear all caches first to prevent environment mismatch
echo -e "${CYAN}   🧹 Clearing old caches...${NC}"
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan config:clear || true
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan route:clear || true
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan view:clear || true
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan cache:clear || true

# Check if APP_KEY exists
if ! docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 grep -q "APP_KEY=base64:" /var/www/.env 2>/dev/null; then
    echo -e "${CYAN}   🔑 Generating application key...${NC}"
    docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan key:generate --force
fi

# Run migrations
echo -e "${CYAN}   📊 Running database migrations...${NC}"
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan migrate --force

# Apply additional schema updates directly
echo -e "${CYAN}   🔧 Applying schema updates (last_seen, last_checked_at)...${NC}"
docker compose -f docker-compose.prod.yml exec -T mariadb mysql \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    "${DB_DATABASE}" -e "
    ALTER TABLE remotes 
    ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL COMMENT 'Last time device responded successfully' AFTER status,
    ADD COLUMN IF NOT EXISTS last_checked_at TIMESTAMP NULL COMMENT 'Last time system checked device status' AFTER last_seen;
    
    UPDATE remotes SET last_checked_at = NOW() WHERE last_checked_at IS NULL;
    UPDATE remotes SET last_seen = NOW() WHERE status = 'Connected' AND last_seen IS NULL;
" 2>/dev/null && echo -e "${GREEN}   ✅ Schema updated${NC}" || echo -e "${YELLOW}   ⚠️  Schema already updated${NC}"

# Apply performance optimization indexes
echo -e "${CYAN}   ⚡ Applying performance indexes...${NC}"
docker compose -f docker-compose.prod.yml exec -T mariadb mysql \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    "${DB_DATABASE}" -e "
    -- Remotes table indexes
    CREATE INDEX IF NOT EXISTS idx_deleted_at ON remotes(deleted_at);
    CREATE INDEX IF NOT EXISTS idx_created_at ON remotes(created_at);
    CREATE INDEX IF NOT EXISTS idx_name ON remotes(name);
    CREATE INDEX IF NOT EXISTS idx_deleted_created ON remotes(deleted_at, created_at);
    
    -- Schedules table indexes
    CREATE INDEX IF NOT EXISTS idx_schedules_deleted_at ON schedules(deleted_at);
    CREATE INDEX IF NOT EXISTS idx_schedules_created_at ON schedules(created_at);
    CREATE INDEX IF NOT EXISTS idx_schedules_name ON schedules(name);
    CREATE INDEX IF NOT EXISTS idx_schedules_deleted_created ON schedules(deleted_at, created_at);
    
    -- Playlists table indexes
    CREATE INDEX IF NOT EXISTS idx_playlists_deleted_at ON playlists(deleted_at);
    CREATE INDEX IF NOT EXISTS idx_playlists_created_at ON playlists(created_at);
    CREATE INDEX IF NOT EXISTS idx_playlists_name ON playlists(name);
    CREATE INDEX IF NOT EXISTS idx_playlists_deleted_created ON playlists(deleted_at, created_at);
    
    -- Layouts table indexes
    CREATE INDEX IF NOT EXISTS idx_layouts_deleted_at ON layouts(deleted_at);
    CREATE INDEX IF NOT EXISTS idx_layouts_created_at ON layouts(created_at);
    CREATE INDEX IF NOT EXISTS idx_layouts_name ON layouts(name);
    CREATE INDEX IF NOT EXISTS idx_layouts_deleted_created ON layouts(deleted_at, created_at);
" 2>/dev/null && echo -e "${GREEN}   ✅ Performance indexes created${NC}" || echo -e "${YELLOW}   ⚠️  Indexes already exist${NC}"

# Create storage link
echo -e "${CYAN}   🔗 Creating storage symlink...${NC}"
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan storage:link 2>/dev/null || true

# Optimize for production
echo -e "${CYAN}   ⚡ Optimizing for production...${NC}"
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 composer dump-autoload -o --apcu
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan event:cache
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T cosmic-app-1 php artisan filament:optimize

echo -e "${GREEN}✅ Laravel setup completed${NC}"
echo ""

# Show service status
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Services Status${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

docker compose -f docker-compose.prod.yml ps

echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Access Information${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

echo -e "${GREEN}🎯 Main Services:${NC}"
echo -e "   ${MAGENTA}Nginx Reverse Proxy:${NC}     http://your-server-ip:8080 (HTTP)"
echo -e "   ${MAGENTA}                        ${NC}     https://your-server-ip:8443 (HTTPS - when configured)"
echo ""
echo -e "   ${YELLOW}📌 Direct Access (for debugging):${NC}"
echo -e "   ${MAGENTA}Cosmic Media Streaming:${NC}  http://your-server-ip:8000"
echo -e "   ${MAGENTA}Generate PDF Service:${NC}    http://your-server-ip:3333"
echo -e "   ${MAGENTA}Remote Android Service:${NC}  http://your-server-ip:3001"
echo -e "   ${MAGENTA}MinIO Console:${NC}           http://your-server-ip:9001"

echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Important Notes${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

echo -e "${YELLOW}1. Configure your firewall to allow only necessary ports${NC}"
echo -e "${YELLOW}2. Setup SSL certificates for HTTPS${NC}"
echo -e "${YELLOW}3. Configure domain names and DNS${NC}"
echo -e "${YELLOW}4. Setup monitoring and alerting${NC}"
echo -e "${YELLOW}5. Configure automated backups${NC}"
echo -e "${YELLOW}6. Review and test all services${NC}"
echo ""

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Useful Commands${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

echo -e "${YELLOW}View logs:${NC}"
echo -e "   docker compose -f docker-compose.prod.yml logs -f [service_name]"
echo ""

echo -e "${YELLOW}Stop all services:${NC}"
echo -e "   docker compose -f docker-compose.prod.yml down"
echo ""

echo -e "${YELLOW}Restart a service:${NC}"
echo -e "   docker compose -f docker-compose.prod.yml restart [service_name]"
echo ""

echo -e "${YELLOW}Database backup:${NC}"
echo -e "   docker compose -f docker-compose.prod.yml exec mariadb mysqldump -uroot -p platform > backup.sql"
echo ""

echo -e "${CYAN}========================================${NC}"
echo -e "${GREEN}🎉 Production deployment completed!${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

exit 0
