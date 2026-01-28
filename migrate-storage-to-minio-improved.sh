#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Detect environment (default to production)
ENVIRONMENT="${1:-prod}"

if [[ "$ENVIRONMENT" != "dev" && "$ENVIRONMENT" != "prod" ]]; then
    echo -e "${RED}❌ Invalid environment. Use: dev or prod${NC}"
    echo -e "Usage: $0 [dev|prod]"
    exit 1
fi

COMPOSE_FILE="docker-compose.${ENVIRONMENT}.yml"
MINIO_CONTAINER="platform-minio-${ENVIRONMENT}"
ENV_FILE=".env.${ENVIRONMENT}"

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Storage Migration to MinIO (Improved)${NC}"
echo -e "${CYAN}  Environment: ${ENVIRONMENT^^}${NC}"
echo -e "${CYAN}  Archive → MinIO via API${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

# Configuration
BACKUP_FILE="${2:-myfolder.tar.zst}"
EXTRACT_DIR="storage-temp"
MINIO_BUCKET="cms"
MINIO_HOST="localhost:9000"
UPLOAD_DELAY=0.3  # Delay between files to avoid throttling
LOG_FILE="migrate-minio.log"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ Backup file not found: $BACKUP_FILE${NC}"
    echo -e "${YELLOW}Usage: $0 [dev|prod] [archive-file]${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Backup file found: $BACKUP_FILE ($(du -h $BACKUP_FILE | cut -f1))${NC}"
echo ""

# Check if MinIO is running
if ! docker ps | grep -q "$MINIO_CONTAINER"; then
    echo -e "${RED}❌ MinIO container is not running${NC}"
    echo -e "${YELLOW}Please start MinIO first:${NC}"
    echo -e "   cd /home/ubuntu/kiosk"
    echo -e "   docker compose -f $COMPOSE_FILE up -d minio"
    exit 1
fi

echo -e "${GREEN}✅ MinIO is running${NC}"
echo ""

# Check disk space
REQUIRED_SPACE_GB=100
AVAILABLE_SPACE_GB=$(df -BG . | tail -1 | awk '{print $4}' | sed 's/G//')
if [ "$AVAILABLE_SPACE_GB" -lt "$REQUIRED_SPACE_GB" ]; then
    echo -e "${RED}❌ Insufficient disk space${NC}"
    echo -e "${YELLOW}Required: ${REQUIRED_SPACE_GB}GB, Available: ${AVAILABLE_SPACE_GB}GB${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Sufficient disk space available (${AVAILABLE_SPACE_GB}GB)${NC}"
echo ""

# Step 1: Extract backup
echo -e "${BLUE}📦 Step 1: Extracting backup file...${NC}"

if [ -d "$EXTRACT_DIR" ]; then
    echo -e "${YELLOW}⚠️  Extract directory already exists. Skip extraction? (Y/n)${NC}"
    read -r -p "   > " SKIP_EXTRACT
    if [[ ! "$SKIP_EXTRACT" =~ ^[Nn]$ ]]; then
        echo -e "${CYAN}   Skipping extraction, using existing files...${NC}"
    else
        echo -e "${CYAN}   Removing old extraction...${NC}"
        rm -rf "$EXTRACT_DIR"
        mkdir -p "$EXTRACT_DIR"
        
        # Detect compression type
        if [[ "$BACKUP_FILE" == *.zst ]]; then
            echo -e "${CYAN}   Extracting zstd archive...${NC}"
            tar -I zstd -xf "$BACKUP_FILE" -C "$EXTRACT_DIR"
        elif [[ "$BACKUP_FILE" == *.gz ]]; then
            echo -e "${CYAN}   Extracting gzip archive...${NC}"
            tar -xzf "$BACKUP_FILE" -C "$EXTRACT_DIR"
        else
            echo -e "${CYAN}   Extracting uncompressed archive...${NC}"
            tar -xf "$BACKUP_FILE" -C "$EXTRACT_DIR"
        fi
    fi
else
    mkdir -p "$EXTRACT_DIR"
    
    # Detect compression type
    if [[ "$BACKUP_FILE" == *.zst ]]; then
        echo -e "${CYAN}   Extracting zstd archive...${NC}"
        tar -I zstd -xf "$BACKUP_FILE" -C "$EXTRACT_DIR"
    elif [[ "$BACKUP_FILE" == *.gz ]]; then
        echo -e "${CYAN}   Extracting gzip archive...${NC}"
        tar -xzf "$BACKUP_FILE" -C "$EXTRACT_DIR"
    else
        echo -e "${CYAN}   Extracting uncompressed archive...${NC}"
        tar -xf "$BACKUP_FILE" -C "$EXTRACT_DIR"
    fi
fi

echo ""
echo -e "${GREEN}✅ Extraction completed${NC}"
echo ""

# Find the actual storage path
STORAGE_PATH=$(find "$EXTRACT_DIR" -type d -path "*/storage/app/public" | head -1)
if [ -z "$STORAGE_PATH" ]; then
    echo -e "${YELLOW}⚠️  storage/app/public not found, using root of extract dir${NC}"
    STORAGE_PATH="$EXTRACT_DIR"
fi

echo -e "${CYAN}   Upload path: $STORAGE_PATH${NC}"

# Check extracted files
EXTRACTED_FILES=$(find "$STORAGE_PATH" -type f | wc -l)
EXTRACTED_SIZE=$(du -sh "$STORAGE_PATH" | cut -f1)
echo -e "${CYAN}   Files found: $EXTRACTED_FILES${NC}"
echo -e "${CYAN}   Total size: $EXTRACTED_SIZE${NC}"
echo ""

# Step 2: Setup MinIO Client
echo -e "${BLUE}📥 Step 2: Setting up MinIO Client...${NC}"

if ! command -v mc &> /dev/null; then
    echo -e "${YELLOW}   Installing MinIO Client...${NC}"
    curl -sL https://dl.min.io/client/mc/release/linux-amd64/mc -o /tmp/mc
    chmod +x /tmp/mc
    sudo mv /tmp/mc /usr/local/bin/mc
    echo -e "${GREEN}   ✅ MinIO Client installed${NC}"
else
    echo -e "${GREEN}   ✅ MinIO Client already installed${NC}"
fi
echo ""

# Step 3: Configure MinIO Client
echo -e "${BLUE}⚙️  Step 3: Configuring MinIO Client...${NC}"

# Get MinIO credentials from .env file
source <(grep -E '^MINIO_(KEY|SECRET)=' "$ENV_FILE")

if [ -z "$MINIO_KEY" ] || [ -z "$MINIO_SECRET" ]; then
    echo -e "${RED}❌ MinIO credentials not found in $ENV_FILE${NC}"
    exit 1
fi

echo -e "${CYAN}   MinIO Access Key: ${MINIO_KEY:0:10}...${NC}"
echo -e "${CYAN}   MinIO Endpoint: http://$MINIO_HOST${NC}"

# Configure mc alias
mc alias set local http://$MINIO_HOST "$MINIO_KEY" "$MINIO_SECRET" --api S3v4 2>/dev/null || true

# Test connection
if ! mc ls local/ >/dev/null 2>&1; then
    echo -e "${RED}❌ Cannot connect to MinIO${NC}"
    exit 1
fi

echo -e "${GREEN}✅ MinIO Client configured and connected${NC}"
echo ""

# Step 4: Prepare bucket
echo -e "${BLUE}🪣 Step 4: Preparing MinIO bucket...${NC}"

if mc ls local/$MINIO_BUCKET 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Bucket '$MINIO_BUCKET' already exists${NC}"
    echo -e "${YELLOW}   Clear existing bucket content? (y/N)${NC}"
    read -r -p "   > " CLEAR_BUCKET
    if [[ "$CLEAR_BUCKET" =~ ^[Yy]$ ]]; then
        echo -e "${CYAN}   Clearing bucket...${NC}"
        mc rm --recursive --force local/$MINIO_BUCKET/ || true
        echo -e "${GREEN}   ✅ Bucket cleared${NC}"
    fi
else
    echo -e "${CYAN}   Creating bucket...${NC}"
    mc mb local/$MINIO_BUCKET
    echo -e "${GREEN}✅ Bucket '$MINIO_BUCKET' created${NC}"
fi
echo ""

# Step 5: Upload files to MinIO with retry and throttling
echo -e "${BLUE}☁️  Step 5: Uploading files to MinIO via API...${NC}"
echo -e "${CYAN}   Using delay: ${UPLOAD_DELAY}s between files${NC}"
echo -e "${CYAN}   Log file: $LOG_FILE${NC}"
echo ""

# Start upload log
echo "=== MinIO Upload Log ===" > "$LOG_FILE"
echo "Started: $(date)" >> "$LOG_FILE"
echo "" >> "$LOG_FILE"

# Upload with progress and error handling
UPLOAD_SUCCESS=0
UPLOAD_FAILED=0

# Function to upload a single file with retry
upload_file() {
    local file="$1"
    local relative_path="${file#$STORAGE_PATH/}"
    local max_retries=3
    local retry_count=0
    
    while [ $retry_count -lt $max_retries ]; do
        if mc cp "$file" "local/$MINIO_BUCKET/$relative_path" >> "$LOG_FILE" 2>&1; then
            echo -e "${GREEN}✓${NC} $relative_path"
            echo "SUCCESS: $relative_path" >> "$LOG_FILE"
            return 0
        else
            retry_count=$((retry_count + 1))
            echo -e "${YELLOW}⚠${NC} Retry $retry_count/$max_retries: $relative_path"
            echo "RETRY $retry_count: $relative_path" >> "$LOG_FILE"
            sleep 1
        fi
    done
    
    echo -e "${RED}✗${NC} Failed: $relative_path"
    echo "FAILED: $relative_path" >> "$LOG_FILE"
    return 1
}

# Upload files
while IFS= read -r file; do
    if upload_file "$file"; then
        UPLOAD_SUCCESS=$((UPLOAD_SUCCESS + 1))
    else
        UPLOAD_FAILED=$((UPLOAD_FAILED + 1))
    fi
    
    # Progress indicator
    if [ $((($UPLOAD_SUCCESS + $UPLOAD_FAILED) % 10)) -eq 0 ]; then
        echo -e "${CYAN}   Progress: $UPLOAD_SUCCESS succeeded, $UPLOAD_FAILED failed${NC}"
    fi
    
    # Throttling delay
    sleep "$UPLOAD_DELAY"
done < <(find "$STORAGE_PATH" -type f)

echo ""
echo -e "${GREEN}✅ Upload completed${NC}"
echo -e "${CYAN}   Success: $UPLOAD_SUCCESS files${NC}"
echo -e "${CYAN}   Failed: $UPLOAD_FAILED files${NC}"
echo ""

# Log summary
echo "" >> "$LOG_FILE"
echo "Completed: $(date)" >> "$LOG_FILE"
echo "Success: $UPLOAD_SUCCESS" >> "$LOG_FILE"
echo "Failed: $UPLOAD_FAILED" >> "$LOG_FILE"

# Step 6: Verify upload
echo -e "${BLUE}🔍 Step 6: Verifying upload...${NC}"

UPLOADED_COUNT=$(mc ls --recursive local/$MINIO_BUCKET | wc -l)
UPLOADED_SIZE=$(mc du local/$MINIO_BUCKET | awk '{print $1, $2}')

echo -e "${CYAN}   Files in MinIO: $UPLOADED_COUNT${NC}"
echo -e "${CYAN}   Bucket size: $UPLOADED_SIZE${NC}"
echo -e "${CYAN}   Expected files: $EXTRACTED_FILES${NC}"

if [ "$UPLOAD_FAILED" -eq 0 ]; then
    echo -e "${GREEN}✅ All files uploaded successfully${NC}"
else
    echo -e "${YELLOW}⚠️  $UPLOAD_FAILED files failed. Check $LOG_FILE for details.${NC}"
fi
echo ""

# Step 7: Cleanup
echo -e "${BLUE}🧹 Step 7: Cleanup${NC}"
echo -e "${YELLOW}   Remove extracted files from $EXTRACT_DIR? (y/N)${NC}"
read -r -p "   > " CLEANUP_RESPONSE

if [[ "$CLEANUP_RESPONSE" =~ ^[Yy]$ ]]; then
    echo -e "${CYAN}   Removing $EXTRACT_DIR...${NC}"
    rm -rf "$EXTRACT_DIR"
    echo -e "${GREEN}   ✅ Cleanup completed${NC}"
else
    echo -e "${YELLOW}   ⚠️  Keeping extracted files in $EXTRACT_DIR${NC}"
fi
echo ""

# Summary
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Migration Summary${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""
echo -e "${GREEN}✅ Files extracted: $EXTRACTED_FILES${NC}"
echo -e "${GREEN}✅ Files uploaded: $UPLOAD_SUCCESS${NC}"
if [ "$UPLOAD_FAILED" -gt 0 ]; then
    echo -e "${RED}❌ Files failed: $UPLOAD_FAILED${NC}"
fi
echo -e "${GREEN}✅ MinIO bucket: $MINIO_BUCKET ($UPLOADED_SIZE)${NC}"
echo ""
echo -e "${YELLOW}📝 Next Steps:${NC}"
echo -e "   1. Access MinIO Console: http://your-server:9001"
echo -e "      Username: $MINIO_KEY"
echo -e "      Password: [your MinIO secret]"
echo -e "   2. Verify files in GUI"
echo -e "   3. Test application file access"
if [ "$UPLOAD_FAILED" -gt 0 ]; then
    echo -e "   4. ${RED}Check failed files in: $LOG_FILE${NC}"
    echo -e "   5. ${YELLOW}Re-run script or manually upload failed files${NC}"
fi
echo ""
echo -e "${GREEN}🎉 Migration completed!${NC}"
echo ""
