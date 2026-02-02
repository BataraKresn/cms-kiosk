#!/bin/bash

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}🧹 Clear Application Logs${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# Function to clear Laravel logs
clear_laravel_logs() {
    local container=$1
    echo -e "${YELLOW}Clearing logs in: ${container}${NC}"
    
    # Check if container is running
    if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
        # Clear Laravel logs
        docker exec "$container" bash -c "
            if [ -f /var/www/storage/logs/laravel.log ]; then
                echo '=== Logs cleared on $(date) ===' > /var/www/storage/logs/laravel.log
                echo '✓ Laravel logs cleared'
            else
                echo '⚠ Laravel log file not found'
            fi
        " 2>/dev/null
        
        if [ $? -eq 0 ]; then
            echo -e "  ${GREEN}✓ Logs cleared${NC}"
        else
            echo -e "  ${RED}✗ Failed to clear logs${NC}"
        fi
    else
        echo -e "  ${YELLOW}⚠ Container not running${NC}"
    fi
}

# Function to clear Nginx access logs
clear_nginx_logs() {
    echo -e "${YELLOW}Clearing Nginx logs...${NC}"
    
    # Clear access logs on host (if mounted)
    if [ -d "data-kiosk/nginx/logs" ]; then
        if [ -w "data-kiosk/nginx/logs/access.log" ] 2>/dev/null; then
            > data-kiosk/nginx/logs/access.log && echo -e "  ${GREEN}✓ Access log cleared${NC}"
        elif [ -f "data-kiosk/nginx/logs/access.log" ]; then
            sudo truncate -s 0 data-kiosk/nginx/logs/access.log 2>/dev/null && echo -e "  ${GREEN}✓ Access log cleared (sudo)${NC}" || echo -e "  ${YELLOW}⚠ Access log: permission denied${NC}"
        fi
        
        if [ -w "data-kiosk/nginx/logs/error.log" ] 2>/dev/null; then
            > data-kiosk/nginx/logs/error.log && echo -e "  ${GREEN}✓ Error log cleared${NC}"
        elif [ -f "data-kiosk/nginx/logs/error.log" ]; then
            sudo truncate -s 0 data-kiosk/nginx/logs/error.log 2>/dev/null && echo -e "  ${GREEN}✓ Error log cleared (sudo)${NC}" || echo -e "  ${YELLOW}⚠ Error log: permission denied${NC}"
        fi
    fi
    
    # Clear Nginx logs inside containers (more reliable)
    for container in cosmic-nginx-prod; do
        if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
            docker exec "$container" bash -c "
                > /var/log/nginx/access.log 2>/dev/null
                > /var/log/nginx/error.log 2>/dev/null
                echo 'Nginx logs cleared inside container'
            " 2>/dev/null && echo -e "  ${GREEN}✓ Container nginx logs cleared${NC}" || echo -e "  ${YELLOW}⚠ Container not accessible${NC}"
        fi
    done
}

# Function to clear Docker container logs
clear_docker_logs() {
    echo -e "${YELLOW}Clearing Docker container logs...${NC}"
    
    # Get all running containers
    containers=$(docker ps --format '{{.Names}}' | grep -E 'cosmic-|remote-|generate-pdf')
    
    if [ -z "$containers" ]; then
        echo -e "  ${YELLOW}⚠ No running containers found${NC}"
        return
    fi
    
    for container in $containers; do
        log_file=$(docker inspect --format='{{.LogPath}}' "$container" 2>/dev/null)
        if [ -n "$log_file" ] && [ -f "$log_file" ]; then
            # Truncate log file (requires root or proper permissions)
            sudo truncate -s 0 "$log_file" 2>/dev/null && echo -e "  ${GREEN}✓ ${container}${NC}" || echo -e "  ${YELLOW}⚠ ${container} (permission denied)${NC}"
        fi
    done
}

# Function to clear old logs (7+ days)
clear_old_logs() {
    echo -e "${YELLOW}Clearing old logs (7+ days)...${NC}"
    
    # Clear old Laravel logs if rotated
    find data-kiosk/prod/storage/logs -name "*.log" -mtime +7 -delete 2>/dev/null && echo -e "  ${GREEN}✓ Old Laravel logs deleted${NC}" || echo -e "  ${YELLOW}⚠ No old Laravel logs${NC}"
    
    # Clear old Nginx logs
    find data-kiosk/nginx/logs -name "*.log.*" -mtime +7 -delete 2>/dev/null && echo -e "  ${GREEN}✓ Old Nginx logs deleted${NC}" || echo -e "  ${YELLOW}⚠ No old Nginx logs${NC}"
    
    # Clear old backup logs
    find data-kiosk/backups -name "*.log" -mtime +30 -delete 2>/dev/null && echo -e "  ${GREEN}✓ Old backup logs deleted${NC}" || echo -e "  ${YELLOW}⚠ No old backup logs${NC}"
}

# Main execution
echo -e "${BLUE}Step 1/5: Clear Laravel application logs${NC}"
clear_laravel_logs "cosmic-app-1-prod"
clear_laravel_logs "cosmic-app-2-prod"
clear_laravel_logs "cosmic-app-3-prod"
echo ""

echo -e "${BLUE}Step 2/5: Clear Nginx logs${NC}"
clear_nginx_logs
echo ""

echo -e "${BLUE}Step 3/5: Clear Docker container logs${NC}"
echo -e "${CYAN}(This requires sudo permissions)${NC}"
clear_docker_logs
echo ""

echo -e "${BLUE}Step 4/5: Clear old rotated logs${NC}"
clear_old_logs
echo ""

echo -e "${BLUE}Step 5/5: Display current log sizes${NC}"
echo -e "${YELLOW}Laravel logs:${NC}"
for container in cosmic-app-1-prod cosmic-app-2-prod cosmic-app-3-prod; do
    if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
        size=$(docker exec "$container" du -sh /var/www/storage/logs 2>/dev/null | cut -f1)
        echo -e "  ${container}: ${size}"
    fi
done

echo -e "${YELLOW}Docker logs:${NC}"
docker ps --format '{{.Names}}' | grep -E 'cosmic-|remote-|generate-pdf' | while read container; do
    log_file=$(docker inspect --format='{{.LogPath}}' "$container" 2>/dev/null)
    if [ -n "$log_file" ] && [ -f "$log_file" ]; then
        size=$(du -sh "$log_file" 2>/dev/null | cut -f1)
        echo -e "  ${container}: ${size}"
    fi
done

echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${GREEN}✅ Log cleanup completed!${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""
echo -e "${YELLOW}💡 Tips:${NC}"
echo -e "  • Run this before deployment to start fresh"
echo -e "  • Schedule with cron: ${CYAN}0 0 * * 0 /path/to/clear-logs.sh${NC}"
echo -e "  • For Docker logs, run: ${CYAN}sudo bash clear-logs.sh${NC}"
echo ""
