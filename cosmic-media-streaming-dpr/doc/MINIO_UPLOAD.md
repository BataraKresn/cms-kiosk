# 📦 File Upload Configuration - MinIO

## Summary

**All file uploads sekarang otomatis disimpan ke MinIO object storage.**

### Changes Made:

1. ✅ **MediaController** - Updated to use MinIO for all uploads
2. ✅ **Filesystem Config** - MinIO set as default disk
3. ✅ **Docker Compose** - MinIO service included
4. ✅ **Environment** - MinIO variables configured

---

## Upload Locations

| File Type | MinIO Path | Example |
|-----------|-----------|---------|
| **Videos** | `videos/` | `videos/sample.mp4` |
| **Images** | `images/` | `images/photo.jpg` |
| **PDFs** | `pdfs/` | `pdfs/document.pdf` |
| **HTML** | `html/` | `html/content.html` |
| **Others** | `files/` | `files/archive.zip` |

---

## Upload Flow

```
User Upload
    ↓
Chunk Upload Handler
    ↓
File Validation (type, size)
    ↓
Sanitize Filename
    ↓
Upload to MinIO → videos/filename.mp4
    ↓
Generate Public URL
    ↓
Return Response
```

---

## Configuration

### .env File:
```env
# Default storage disk
FILESYSTEM_DISK=minio

# MinIO Configuration
MINIO_ENDPOINT=http://localhost:9000
MINIO_KEY=minioadmin
MINIO_SECRET=minioadmin
MINIO_BUCKET=cosmic-media

# AWS S3 compatibility (points to MinIO)
AWS_ACCESS_KEY_ID=${MINIO_KEY}
AWS_SECRET_ACCESS_KEY=${MINIO_SECRET}
AWS_ENDPOINT=${MINIO_ENDPOINT}
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### Docker:
```env
# Inside Docker, use internal hostname
MINIO_ENDPOINT=http://minio:9000
```

---

## Access Uploaded Files

### 1. Public URL (Direct Access)
```
http://localhost:9000/cosmic-media/videos/sample.mp4
```

### 2. Temporary URL (From Laravel)
```php
$url = Storage::disk('minio')->temporaryUrl(
    'videos/sample.mp4',
    now()->addMinutes(30)
);
```

### 3. MinIO Console (Browser)
```
http://localhost:9001
Login: minioadmin / minioadmin
```

---

## Code Examples

### Upload File:
```php
// In MediaController@store
$file = $request->file('file');

// Automatically uploads to MinIO
$path = Storage::disk('minio')->putFileAs(
    'videos',
    $file,
    $filename,
    'public'
);

// Returns: videos/filename.mp4
```

### Get File URL:
```php
// Public URL
$url = Storage::disk('minio')->url('videos/sample.mp4');
// http://localhost:9000/cosmic-media/videos/sample.mp4

// Temporary URL (expires)
$url = Storage::disk('minio')->temporaryUrl(
    'videos/sample.mp4',
    now()->addMinutes(30)
);
```

### Check if File Exists:
```php
if (Storage::disk('minio')->exists('videos/sample.mp4')) {
    // File exists in MinIO
}
```

### Download File:
```php
return Storage::disk('minio')->download('videos/sample.mp4');
```

### Delete File:
```php
Storage::disk('minio')->delete('videos/sample.mp4');
```

---

## Validation

Files are validated before upload:

```php
// Allowed MIME types
$allowedMimes = [
    'video/mp4',
    'video/mpeg',
    'video/quicktime',
    'video/x-msvideo',
    'video/x-ms-wmv',
    'video/webm',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'text/html',
];

// Max file size
$maxSize = 10 * 1024 * 1024 * 1024; // 10GB
```

---

## Fallback to Local Storage

If MinIO is unavailable, files automatically fall back to local storage:

```php
try {
    // Try MinIO first
    return $this->saveFileToMinIO($file);
} catch (\Exception $e) {
    // Fallback to local
    return $this->saveFileToLocal($file);
}
```

---

## Migration from Local Storage

### Option 1: Manual Migration via Console

```bash
# Access MinIO console
http://localhost:9001

# Upload files from storage/app/public/
# Drag and drop to cosmic-media bucket
```

### Option 2: Using MinIO Client

```bash
# Inside Docker
docker-compose exec minio-client bash

# Upload directory
mc cp --recursive /var/www/storage/app/public/ minio/cosmic-media/

# Verify
mc ls minio/cosmic-media/
```

### Option 3: Using Laravel Command (TODO)

```bash
php artisan storage:migrate-to-minio
```

---

## Troubleshooting

### Upload fails with "Failed to upload to MinIO"

**Check:**
1. MinIO is running: `docker-compose ps minio`
2. Bucket exists: Access http://localhost:9001
3. Environment variables are correct in .env
4. Check logs: `docker-compose logs minio`

**Fix:**
```bash
# Restart MinIO
docker-compose restart minio

# Recreate bucket
docker-compose exec minio-client mc mb minio/cosmic-media --ignore-existing

# Check connection
docker-compose exec app php artisan tinker
>>> Storage::disk('minio')->exists('test.txt')
```

### Can't access uploaded files

**Check:**
1. Bucket has public download policy
2. File path is correct (check MinIO console)
3. MinIO endpoint is accessible

**Fix:**
```bash
# Set public policy
docker-compose exec minio-client mc anonymous set download minio/cosmic-media

# Or use temporary URL instead of public URL
Storage::disk('minio')->temporaryUrl($path, now()->addMinutes(30));
```

### Large file upload fails

**Check:**
1. Nginx/PHP upload limits
2. Network timeout
3. MinIO storage space

**Fix in Dockerfile:**
```dockerfile
RUN echo "upload_max_filesize = 10G\n" \
         "post_max_size = 10G\n" \
         "max_execution_time = 300\n" \
    >> /usr/local/etc/php/conf.d/uploads.ini
```

---

## Performance Tips

### 1. Use Temporary URLs for Private Files
```php
// Instead of public URL
$url = Storage::disk('minio')->temporaryUrl($path, now()->addHours(1));
```

### 2. Lazy Load Images
```html
<img src="{{ Storage::disk('minio')->url($path) }}" loading="lazy" />
```

### 3. Use CDN (Optional)
- Setup CloudFlare or similar CDN
- Point to MinIO endpoint
- Configure in .env

### 4. Compress Images Before Upload
```php
use Intervention\Image\Facades\Image;

$image = Image::make($file)
    ->resize(1920, null, function ($constraint) {
        $constraint->aspectRatio();
    })
    ->encode('webp', 80);

Storage::disk('minio')->put($path, $image);
```

---

## Security Considerations

### Production Checklist:

- [ ] Change MinIO credentials from default
- [ ] Use strong passwords (20+ characters)
- [ ] Don't expose MinIO port (9000) publicly
- [ ] Use temporary URLs for sensitive files
- [ ] Enable SSL/TLS for MinIO
- [ ] Setup firewall rules
- [ ] Regular backups of MinIO data
- [ ] Monitor storage usage
- [ ] Implement file access logging

### Example: Secure MinIO Setup

```env
# Use strong credentials
MINIO_KEY=your_complex_key_here
MINIO_SECRET=your_very_long_and_complex_secret_here

# Use HTTPS in production
MINIO_ENDPOINT=https://minio.yourdomain.com
```

---

## Backup Strategy

```bash
# Backup MinIO volume
docker run --rm \
  -v cosmic-media-streaming-dpr_minio_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/minio_backup_$(date +%Y%m%d).tar.gz /data

# Restore MinIO volume
docker run --rm \
  -v cosmic-media-streaming-dpr_minio_data:/data \
  -v $(pwd):/backup \
  alpine sh -c "cd /data && tar xzf /backup/minio_backup_YYYYMMDD.tar.gz --strip 1"
```

---

## API Reference

See [MediaController.php](app/Http/Controllers/MediaController.php) for implementation details.

**Key Methods:**
- `store()` - Handle chunk upload
- `validateUploadedFile()` - Validate file type/size
- `saveFileToMinIO()` - Upload to MinIO
- `saveFileToLocal()` - Fallback to local
- `getFolderByMimeType()` - Determine folder by MIME
- `sanitizeFilename()` - Clean filename

---

**For full Docker documentation, see:** [DOCKER_GUIDE.md](DOCKER_GUIDE.md)
