# MinIO Upload Migration Script

## 📦 Overview
Script untuk migrasi file dari local storage ke MinIO

## 🚀 Usage

```bash
# Inside Docker container
docker-compose exec app php artisan storage:migrate-to-minio

# Or locally
php artisan storage:migrate-to-minio --dry-run
```

## 📝 What it does:

1. Scan `storage/app/public` untuk semua file
2. Upload ke MinIO dengan structure:
   - videos/ → video files
   - images/ → image files
   - files/ → other files
3. Update database records dengan new paths
4. Backup original files (optional)
5. Verify uploads

## ⚠️ Before Running:

- Backup database
- Ensure MinIO is running
- Check .env configuration
- Test with --dry-run first

## 🔧 Manual Migration:

```bash
# Access MinIO client
docker-compose exec minio-client bash

# Upload directory
mc cp --recursive /path/to/files minio/cosmic-media/

# Verify
mc ls minio/cosmic-media/
```
