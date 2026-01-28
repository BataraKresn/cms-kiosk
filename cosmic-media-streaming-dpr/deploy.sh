#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Cosmic Media Streaming Deployment${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found. Copying from .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✅ .env file created. Please configure it before continuing.${NC}"
    echo -e "${YELLOW}⚠️  Run this script again after configuring .env${NC}"
    exit 1
fi

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running. Please start Docker and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}🐳 Docker is running${NC}"
echo ""

# Ask deployment mode
echo -e "${YELLOW}Select deployment mode:${NC}"
echo "1) Development (with hot reload)"
echo "2) Production (optimized)"
read -p "Enter choice [1 or 2]: " mode

if [ "$mode" = "1" ]; then
    export APP_ENV=local
    export APP_DEBUG=true
    echo -e "${GREEN}📝 Development mode selected${NC}"
else
    export APP_ENV=production
    export APP_DEBUG=false
    echo -e "${GREEN}🚀 Production mode selected${NC}"
fi
echo ""

# Build and start containers with recreate strategy (zero-downtime)
echo -e "${YELLOW}🔨 Building and updating containers...${NC}"
docker compose build
echo ""

echo -e "${YELLOW}🚀 Recreating containers...${NC}"
docker compose up -d --force-recreate --remove-orphans
echo ""

# Wait for MySQL to be ready
echo -e "${YELLOW}⏳ Waiting for MySQL to be ready...${NC}"
until docker compose exec -T mysql mysqladmin ping -h localhost --silent; do
    echo -n "."
    sleep 2
done
echo ""
echo -e "${GREEN}✅ MySQL is ready${NC}"
echo ""

# Wait for Redis to be ready
echo -e "${YELLOW}⏳ Waiting for Redis to be ready...${NC}"
until docker compose exec -T redis redis-cli ping > /dev/null 2>&1; do
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
    docker compose exec -T app php artisan key:generate --force
    echo -e "${GREEN}✅ Application key generated${NC}"
    echo ""
fi

# Run migrations
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
docker compose exec -T app php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}"
echo ""

# Create storage link
echo -e "${YELLOW}🔗 Creating storage link...${NC}"
docker compose exec -T app php artisan storage:link || true
echo -e "${GREEN}✅ Storage link created${NC}"
echo ""

# Clear and cache config
if [ "$mode" = "2" ]; then
    echo -e "${YELLOW}⚡ Optimizing application for production...${NC}"
    docker compose exec -T app php artisan config:cache
    docker compose exec -T app php artisan route:cache
    docker compose exec -T app php artisan view:cache
    docker compose exec -T app php artisan optimize
    echo -e "${GREEN}✅ Application optimized${NC}"
    echo ""
else
    echo -e "${YELLOW}🧹 Clearing caches for development...${NC}"
    docker compose exec -T app php artisan config:clear
    docker compose exec -T app php artisan route:clear
    docker compose exec -T app php artisan view:clear
    docker compose exec -T app php artisan cache:clear
    echo -e "${GREEN}✅ Caches cleared${NC}"
    echo ""
fi

# Set proper permissions
echo -e "${YELLOW}🔒 Setting proper permissions...${NC}"
docker compose exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

# Show container status
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Container Status${NC}"
echo -e "${GREEN}========================================${NC}"
docker compose ps
echo ""

# Show service URLs
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Service URLs${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "🌐 Application:     ${GREEN}http://localhost:8000${NC}"
echo -e "🎨 Vite Dev Server: ${GREEN}http://localhost:5173${NC}"
echo -e "🗄️  MySQL:           ${GREEN}localhost:3306${NC}"
echo -e "💾 Redis:           ${GREEN}localhost:6379${NC}"
echo -e "📦 MinIO:           ${GREEN}http://localhost:9000${NC}"
echo -e "🎛️  MinIO Console:   ${GREEN}http://localhost:9001${NC}"
echo -e "   Credentials:    ${YELLOW}minioadmin / minioadmin${NC}"
echo ""

# Show logs command
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Useful Commands${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "📋 View logs:        ${YELLOW}docker compose logs -f${NC}"
echo -e "🔍 View app logs:    ${YELLOW}docker compose logs -f app${NC}"
echo -e "🔍 View queue logs:  ${YELLOW}docker compose logs -f queue-worker${NC}"
echo -e "🛑 Stop containers:  ${YELLOW}docker compose stop${NC}"
echo -e "🔄 Restart:          ${YELLOW}docker compose restart${NC}"
echo -e "🐚 Access shell:     ${YELLOW}docker compose exec app bash${NC}"
echo -e "🗃️  Database shell:   ${YELLOW}docker compose exec mysql mysql -u root -p${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✅ Deployment Completed!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Ask if user wants to see logs
read -p "Do you want to see application logs? [y/N]: " show_logs
if [ "$show_logs" = "y" ] || [ "$show_logs" = "Y" ]; then
    docker compose logs -f app
fi
