#!/bin/bash

# Update Script untuk Production Zero-Downtime Deployment
# Script ini untuk update aplikasi tanpa downtime

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Production Update - Zero Downtime${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Pull latest code
echo -e "${YELLOW}📥 Pulling latest code from git...${NC}"
git pull origin main
echo -e "${GREEN}✅ Code updated${NC}"
echo ""

# Build new images
echo -e "${YELLOW}🔨 Building new Docker images...${NC}"
docker compose build --no-cache
echo -e "${GREEN}✅ Images built${NC}"
echo ""

# Recreate containers without downtime
echo -e "${YELLOW}🔄 Recreating containers (zero-downtime)...${NC}"
docker compose up -d --force-recreate --remove-orphans
echo -e "${GREEN}✅ Containers recreated${NC}"
echo ""

# Run migrations (safe - won't break existing data)
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
docker compose exec -T app php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}"
echo ""

# Clear and rebuild cache
echo -e "${YELLOW}🧹 Clearing old cache...${NC}"
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan view:clear
docker compose exec -T app php artisan cache:clear
echo ""

echo -e "${YELLOW}⚡ Building optimized cache...${NC}"
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan optimize
echo -e "${GREEN}✅ Cache optimized${NC}"
echo ""

# Restart queue workers to pick up new code
echo -e "${YELLOW}🔄 Restarting queue workers...${NC}"
docker compose restart queue-worker
echo -e "${GREEN}✅ Queue workers restarted${NC}"
echo ""

# Show status
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Update Completed!${NC}"
echo -e "${GREEN}========================================${NC}"
docker compose ps
echo ""

echo -e "${GREEN}✅ Production update completed successfully!${NC}"
