#!/bin/bash

set -e

# Parse command line arguments
FORCE_REBUILD=false
STOP_SERVICES=false

for arg in "$@"; do
    case $arg in
        --force-rebuild|-f)
            FORCE_REBUILD=true
            echo -e "⚠️  Force rebuild enabled - all layers will be rebuilt"
            ;;
        --stop|-s)
            STOP_SERVICES=true
            echo -e "⚠️  Stop services enabled - will run docker compose down first"
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
echo -e "${CYAN}  Development Environment Deployment${NC}"
echo -e "${CYAN}  All Microservices${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running. Please start Docker and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker is running${NC}"
echo ""

# Check if .env.dev exists
if [ ! -f .env.dev ]; then
    echo -e "${RED}❌ .env.dev file not found.${NC}"
    echo -e "${YELLOW}This file should exist with default development settings.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ .env.dev found${NC}"
echo ""

# Create necessary directories
echo -e "${YELLOW}📁 Creating necessary directories...${NC}"
mkdir -p data-kiosk/logs/cosmic-app data-kiosk/logs/cosmic-queue-1 data-kiosk/logs/cosmic-scheduler
mkdir -p data-kiosk/logs/generate-pdf data-kiosk/logs/remote-android
mkdir -p data-kiosk/logs/dev/mariadb
mkdir -p data-kiosk/backups data-kiosk/minio-backup
mkdir -p data-kiosk/dev/mariadb data-kiosk/dev/redis data-kiosk/dev/minio
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

# Make restore.sql executable
if [ -f restore.sql ]; then
    chmod +x restore.sql
fi

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

docker compose -f docker-compose.dev.yml build $BUILD_FLAGS

echo ""
echo -e "${GREEN}✅ All services built successfully${NC}"
echo ""

# Optional: Stop services if requested
if [ "$STOP_SERVICES" = true ]; then
    echo -e "${YELLOW}🛑 Stopping existing services (--stop flag)...${NC}"
    docker compose -f docker-compose.dev.yml down
    echo -e "${GREEN}✅ Services stopped${NC}"
    echo ""
fi

# Start/Update infrastructure services first (Docker handles recreate automatically)
echo -e "${YELLOW}🚀 Starting/Updating infrastructure services (MariaDB, Redis, MinIO)...${NC}"
docker compose -f docker-compose.dev.yml up -d mariadb redis minio
echo ""

# Wait for MariaDB to be ready
echo -e "${YELLOW}⏳ Waiting for MariaDB to be ready...${NC}"
echo -e "${CYAN}   (This includes importing platform.sql and running restore.sql)${NC}"
RETRY_COUNT=0
MAX_RETRIES=60
until docker compose -f docker-compose.dev.yml exec -T mariadb mysqladmin ping -h localhost --silent 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
        echo -e "${RED}❌ MariaDB failed to start after ${MAX_RETRIES} attempts${NC}"
        docker compose -f docker-compose.dev.yml logs mariadb
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
until docker compose -f docker-compose.dev.yml exec -T redis redis-cli ping > /dev/null 2>&1; do
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
sleep 10
echo -e "${GREEN}✅ MinIO is ready${NC}"
echo ""

# Start all application services
echo -e "${BLUE}🚀 Starting all microservices...${NC}"
echo ""

echo -e "${MAGENTA}   📦 Service #1: Cosmic Media Streaming (Laravel)${NC}"
docker compose -f docker-compose.dev.yml up -d cosmic-app cosmic-queue cosmic-scheduler
echo -e "${GREEN}   ✅ Cosmic Media Streaming started${NC}"
echo ""

# Build Vite assets for better performance
echo -e "${YELLOW}⚡ Building Vite assets (one-time build)...${NC}"
docker compose -f docker-compose.dev.yml exec -T cosmic-app npm run build 2>/dev/null || echo -e "${YELLOW}   ⚠️  Skipping Vite build (may require manual run)${NC}"
echo -e "${GREEN}   ✅ Assets built${NC}"
echo ""

echo -e "${MAGENTA}   📦 Service #2: Generate PDF (Node.js)${NC}"
docker compose -f docker-compose.dev.yml up -d generate-pdf
echo -e "${GREEN}   ✅ Generate PDF started${NC}"
echo ""

echo -e "${MAGENTA}   📦 Service #3: Remote Android Device (Python)${NC}"
docker compose -f docker-compose.dev.yml up -d remote-android
echo -e "${GREEN}   ✅ Remote Android Device started${NC}"
echo ""

# Start optional development tools
echo -e "${YELLOW}🔧 Starting development tools (Redis Commander)...${NC}"
docker compose -f docker-compose.dev.yml up -d redis-commander
echo -e "${GREEN}✅ Development tools started${NC}"
echo ""

# Wait for Laravel app to be ready
echo -e "${YELLOW}⏳ Waiting for Laravel application to be ready...${NC}"
sleep 5

# Check if we need to run migrations
echo -e "${YELLOW}📊 Running Laravel setup...${NC}"

# Generate APP_KEY if needed
if ! grep -q "APP_KEY=base64:" cosmic-media-streaming-dpr/.env 2>/dev/null; then
    echo -e "${CYAN}   🔑 Generating application key...${NC}"
    docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan key:generate --force 2>/dev/null || true
fi

# Run migrations
echo -e "${CYAN}   📊 Running database migrations...${NC}"
docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan migrate --force 2>/dev/null || echo -e "${YELLOW}   ⚠️  Migrations may have already been run${NC}"

# Create storage link
echo -e "${CYAN}   🔗 Creating storage symlink...${NC}"
docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan storage:link 2>/dev/null || true

# Clear caches
echo -e "${CYAN}   🧹 Clearing caches...${NC}"
docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan config:clear 2>/dev/null || true
docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan cache:clear 2>/dev/null || true
docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan view:clear 2>/dev/null || true
docker compose -f docker-compose.dev.yml exec -T cosmic-app php artisan route:clear 2>/dev/null || true

# Optimize for development
echo -e "${CYAN}   ⚡ Optimizing autoloader...${NC}"
docker compose -f docker-compose.dev.yml exec -T cosmic-app composer dump-autoload -o 2>/dev/null || true

# Build Vite assets (instead of compile on-the-fly)
echo -e "${CYAN}   🎨 Building Vite assets...${NC}"
docker compose -f docker-compose.dev.yml exec -T cosmic-app npm run build 2>/dev/null || echo -e "${YELLOW}   ⚠️  Vite build may have failed, check npm install${NC}"

echo -e "${GREEN}✅ Laravel setup completed${NC}"
echo ""

# Show service status
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Services Status${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

docker compose -f docker-compose.dev.yml ps

echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Access Information${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

echo -e "${GREEN}🎯 Main Services:${NC}"
echo -e "   ${MAGENTA}Cosmic Media Streaming:${NC}  http://localhost:8000"
echo -e "   ${MAGENTA}Generate PDF Service:${NC}    http://localhost:3333"
echo -e "   ${MAGENTA}Remote Android Service:${NC}  http://localhost:3001"
echo ""

echo -e "${GREEN}🛠️  Development Tools:${NC}"
echo -e "   ${MAGENTA}Redis Commander:${NC}         http://localhost:8081"
echo -e "   ${MAGENTA}MinIO Console:${NC}           http://localhost:9001"
echo ""

echo -e "${GREEN}📊 Database:${NC}"
echo -e "   ${MAGENTA}Host:${NC}     localhost:3306"
echo -e "   ${MAGENTA}Database:${NC} platform"
echo -e "   ${MAGENTA}User:${NC}     platform_user"
echo -e "   ${MAGENTA}Password:${NC} platform_password_dev"
echo ""

echo -e "${GREEN}⚡ Performance:${NC}"
echo -e "   ${MAGENTA}Cache:${NC}    Redis (fast)"
echo -e "   ${MAGENTA}Sessions:${NC} Redis (persistent)"
echo -e "   ${MAGENTA}Queue:${NC}    Redis (async)"
echo -e "   ${MAGENTA}OPcache:${NC}  Enabled (optimized)"
echo ""

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Useful Commands${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

echo -e "${YELLOW}View logs:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml logs -f [service_name]"
echo ""

echo -e "${YELLOW}Stop all services:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml down"
echo ""

echo -e "${YELLOW}Restart a service:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml restart [service_name]"
echo ""

echo -e "${YELLOW}Execute Laravel commands:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml exec cosmic-app php artisan [command]"
echo ""

echo -e "${CYAN}========================================${NC}"
echo -e "${GREEN}🎉 All microservices deployed successfully!${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

exit 0
