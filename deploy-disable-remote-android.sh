#!/bin/bash

# Remote Android Service Disable - Deployment Script
# Disables deprecated remote-android-prod service

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║    Deploying Remote Android Service Disable                   ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Verify changes
echo -e "${YELLOW}Step 1: Verifying configuration changes...${NC}"
echo ""

echo "Checking docker-compose.prod.yml..."
if grep -q "# - remote-android  # DISABLED: APK sends heartbeat directly to CMS" docker-compose.prod.yml; then
    echo -e "${GREEN}✓ remote-android service disabled in docker-compose${NC}"
else
    echo -e "${RED}✗ Changes not found in docker-compose.prod.yml${NC}"
    echo "  Looking for pattern: # - remote-android  # DISABLED"
    exit 1
fi

echo "Checking nginx.conf..."
if grep -q "# DISABLED: remote-android service deprecated" cosmic-media-streaming-dpr/nginx.conf; then
    echo -e "${GREEN}✓ remote_android_backend upstream disabled in nginx${NC}"
else
    echo -e "${RED}✗ Changes not found in nginx.conf${NC}"
    exit 1
fi

echo "Checking .env.prod..."
if grep -q "# REMOTE_ANDROID_SERVICE_URL" .env.prod; then
    echo -e "${GREEN}✓ REMOTE_ANDROID_SERVICE_URL disabled in .env.prod${NC}"
else
    echo -e "${RED}✗ Changes not found in .env.prod${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}All configuration changes verified!${NC}"
echo ""

# Step 2: Check if remote-android container exists
echo -e "${YELLOW}Step 2: Checking existing containers...${NC}"
echo ""

if docker ps -a | grep -q "remote-android-prod"; then
    echo -e "${YELLOW}⚠ remote-android-prod container exists, stopping and removing...${NC}"
    docker stop remote-android-prod 2>/dev/null || true
    docker rm remote-android-prod 2>/dev/null || true
    echo -e "${GREEN}✓ Container removed${NC}"
else
    echo -e "${GREEN}✓ No remote-android-prod container found (already clean)${NC}"
fi

# Verify no container on port 3001
if docker ps | grep -q ":3001"; then
    echo -e "${RED}✗ WARNING: Another container is using port 3001${NC}"
    docker ps | grep ":3001"
else
    echo -e "${GREEN}✓ Port 3001 is free${NC}"
fi

echo ""

# Step 3: Restart Nginx
echo -e "${YELLOW}Step 3: Restarting Nginx to apply config changes...${NC}"
echo ""

if docker ps | grep -q "platform-nginx-prod"; then
    docker restart platform-nginx-prod
    echo -e "${GREEN}✓ Nginx restarted${NC}"
    
    # Wait for nginx to be healthy
    echo "Waiting for nginx to be healthy..."
    sleep 5
    
    if docker ps | grep -q "platform-nginx-prod"; then
        echo -e "${GREEN}✓ Nginx is running${NC}"
    else
        echo -e "${RED}✗ Nginx failed to start, check logs:${NC}"
        echo "docker logs platform-nginx-prod --tail 50"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠ Nginx not running, you may need to start it manually${NC}"
fi

echo ""

# Step 4: Restart Laravel apps
echo -e "${YELLOW}Step 4: Restarting Laravel application containers...${NC}"
echo ""

for container in cosmic-app-1-prod cosmic-app-2-prod cosmic-app-3-prod; do
    if docker ps | grep -q "$container"; then
        echo "Restarting $container..."
        docker restart $container
        echo -e "${GREEN}✓ $container restarted${NC}"
    else
        echo -e "${YELLOW}⚠ $container not running${NC}"
    fi
done

echo ""

# Step 5: Clear Laravel caches
echo -e "${YELLOW}Step 5: Clearing Laravel caches...${NC}"
echo ""

if docker ps | grep -q "cosmic-app-1-prod"; then
    echo "Clearing config cache..."
    docker exec cosmic-app-1-prod php artisan config:clear
    
    echo "Clearing application cache..."
    docker exec cosmic-app-1-prod php artisan cache:clear
    
    echo "Clearing route cache..."
    docker exec cosmic-app-1-prod php artisan route:clear
    
    echo "Clearing view cache..."
    docker exec cosmic-app-1-prod php artisan view:clear
    
    echo -e "${GREEN}✓ All caches cleared${NC}"
else
    echo -e "${YELLOW}⚠ cosmic-app-1-prod not running, skipping cache clear${NC}"
fi

echo ""

# Step 6: Verification
echo -e "${YELLOW}Step 6: Running verification checks...${NC}"
echo ""

echo "1. Checking no container on port 3001..."
if docker ps | grep -q ":3001"; then
    echo -e "${RED}✗ FAIL: Container still using port 3001${NC}"
    docker ps | grep ":3001"
else
    echo -e "${GREEN}✓ PASS: Port 3001 is free${NC}"
fi

echo ""
echo "2. Checking nginx upstream configuration..."
if docker exec platform-nginx-prod nginx -T 2>/dev/null | grep -q "^    upstream remote_android_backend"; then
    echo -e "${RED}✗ FAIL: remote_android_backend still active in nginx${NC}"
else
    echo -e "${GREEN}✓ PASS: remote_android_backend is commented/disabled in nginx${NC}"
fi

echo ""
echo "3. Checking running containers..."
echo ""
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "(cosmic-app|remote-relay|nginx|mariadb)" || true

echo ""
echo "4. Checking remote-relay-prod is still running (required for remote control)..."
if docker ps | grep -q "remote-relay-prod"; then
    echo -e "${GREEN}✓ PASS: remote-relay-prod is running${NC}"
else
    echo -e "${RED}✗ FAIL: remote-relay-prod not running (needed for remote control!)${NC}"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                   DEPLOYMENT COMPLETE                          ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

echo -e "${GREEN}✅ remote-android-prod service successfully disabled${NC}"
echo ""
echo "Expected Results:"
echo "  • Device status should stay 'Connected' (no flapping)"
echo "  • No 'rate limit exceeded' warnings in logs"
echo "  • Accurate device metrics in CMS"
echo "  • Remote control still works (via remote-relay-prod)"
echo ""
echo "Monitor logs with:"
echo "  docker logs -f cosmic-app-1-prod --tail 50"
echo "  docker logs -f platform-nginx-prod --tail 50"
echo ""
echo "Check device status in CMS:"
echo "  https://kiosk.mugshot.dev/admin/remotes"
echo ""
echo "Documentation:"
echo "  doc/REMOTE_ANDROID_SERVICE_DEPRECATED.md"
echo ""
