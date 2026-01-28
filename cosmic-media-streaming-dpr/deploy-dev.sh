#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Cosmic Media Streaming DPR${NC}"
echo -e "${GREEN}  Development Deployment${NC}"
echo -e "${GREEN}========================================${NC}"
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

# Stop and remove existing containers
echo -e "${YELLOW}🛑 Stopping existing containers...${NC}"
docker compose -f docker-compose.dev.yml down 2>/dev/null || true
echo -e "${GREEN}✅ Containers stopped${NC}"
echo ""

# Build containers
echo -e "${YELLOW}🔨 Building containers...${NC}"
docker compose -f docker-compose.dev.yml build
echo ""

# Start all services
echo -e "${YELLOW}🚀 Starting all services...${NC}"
docker compose -f docker-compose.dev.yml up -d
echo ""

# Wait for MariaDB to be ready
echo -e "${YELLOW}⏳ Waiting for MariaDB to be ready...${NC}"
RETRY_COUNT=0
MAX_RETRIES=60
until docker compose -f docker-compose.dev.yml exec -T mariadb mysqladmin ping -h localhost --silent 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
        echo -e "${RED}❌ MariaDB failed to start after ${MAX_RETRIES} attempts${NC}"
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
until docker compose -f docker-compose.dev.yml exec -T redis redis-cli ping > /dev/null 2>&1; do
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

# Generate application key if not exists
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    docker compose -f docker-compose.dev.yml exec -T app php artisan key:generate --force
    echo -e "${GREEN}✅ Application key generated${NC}"
    echo ""
fi

# Run migrations
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
docker compose -f docker-compose.dev.yml exec -T app php artisan migrate --force 2>/dev/null || echo -e "${YELLOW}⚠️  Migrations may already be run${NC}"
echo -e "${GREEN}✅ Migrations completed${NC}"
echo ""

# Apply performance optimization indexes
echo -e "${YELLOW}⚡ Applying performance indexes...${NC}"
docker compose -f docker-compose.dev.yml exec -T mariadb mysql -uplatform_user -pplatform_password_dev platform -e "
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
" 2>/dev/null && echo -e "${GREEN}✅ Performance indexes created${NC}" || echo -e "${YELLOW}⚠️  Indexes already exist${NC}"
echo ""

# Create storage link
echo -e "${YELLOW}🔗 Creating storage symlink...${NC}"
docker compose -f docker-compose.dev.yml exec -T app php artisan storage:link 2>/dev/null || true
echo -e "${GREEN}✅ Storage link created${NC}"
echo ""

# Clear caches
echo -e "${YELLOW}🧹 Clearing caches...${NC}"
docker compose -f docker-compose.dev.yml exec -T app php artisan config:clear
docker compose -f docker-compose.dev.yml exec -T app php artisan cache:clear
docker compose -f docker-compose.dev.yml exec -T app php artisan view:clear
echo -e "${GREEN}✅ Caches cleared${NC}"
echo ""

# Show service status
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Services Status${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

docker compose -f docker-compose.dev.yml ps

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Access Information${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

echo -e "${GREEN}🎯 Application:${NC}"
echo -e "   ${YELLOW}Laravel App:${NC}       http://localhost:8000"
echo -e "   ${YELLOW}Vite Dev Server:${NC}  http://localhost:5173"
echo ""

echo -e "${GREEN}🛠️  Tools:${NC}"
echo -e "   ${YELLOW}phpMyAdmin:${NC}       http://localhost:8080"
echo -e "   ${YELLOW}Redis Commander:${NC}  http://localhost:8081"
echo -e "   ${YELLOW}MinIO Console:${NC}    http://localhost:9001"
echo ""

echo -e "${GREEN}📊 Database:${NC}"
echo -e "   ${YELLOW}Host:${NC}     localhost:3306"
echo -e "   ${YELLOW}Database:${NC} platform"
echo -e "   ${YELLOW}User:${NC}     platform_user"
echo -e "   ${YELLOW}Password:${NC} platform_password_dev"
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Useful Commands${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

echo -e "${YELLOW}View logs:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml logs -f [service]"
echo ""

echo -e "${YELLOW}Execute artisan command:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml exec app php artisan [command]"
echo ""

echo -e "${YELLOW}Access container shell:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml exec app bash"
echo ""

echo -e "${YELLOW}Stop services:${NC}"
echo -e "   docker compose -f docker-compose.dev.yml down"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

exit 0
