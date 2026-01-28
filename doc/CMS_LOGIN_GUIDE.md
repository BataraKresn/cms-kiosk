# 🔑 CMS Admin Panel Access Guide

## URL Access

### Production
**Main URL**: `http://YOUR_SERVER_IP/admin`
or
**Domain**: `http://your-domain.com/admin`

### Local Development  
**URL**: `http://localhost/admin`

## Default Credentials

**Email**: `administrator@cms.id`
**Password**: Check with your team or reset using command below

## If You Forgot Password

### Method 1: Reset via Artisan Command
```bash
cd /home/ubuntu/kiosk
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan tinker

# In tinker:
$user = App\Models\User::where('email', 'administrator@cms.id')->first();
$user->password = Hash::make('newpassword123');
$user->save();
exit
```

### Method 2: Create New Admin User
```bash
docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan make:filament-user

# Follow prompts:
# Name: Your Name
# Email: youremail@example.com
# Password: ********
```

## Access Remote Control Feature

1. Login to `http://YOUR_IP/admin`
2. Navigate to: **Management > Remotes**
3. Find device with:
   - `Status` = "Connected"
   - `Remote Control Enabled` = true
4. Click **"Remote Control"** button (green icon)
5. New tab will open: `http://YOUR_IP/admin/remotes/{ID}/remote-control`

## Ports Used

- **HTTP**: 80 (Nginx reverse proxy)
- **HTTPS**: 443 (if SSL enabled)
- **Relay HTTP**: 3002
- **Relay WebSocket**: 3003

## Enable Remote Control on Device

```sql
-- Connect to database
docker compose -f docker-compose.prod.yml exec mariadb mysql -ukiosk_platform -p5Kh3dY82ry05 platform

-- Enable remote control
UPDATE remotes 
SET remote_control_enabled = 1, 
    remote_control_port = 5555,
    status = 'Connected'
WHERE id = YOUR_DEVICE_ID;
```

## Troubleshooting

### Can't access /admin
- Check nginx is running: `docker ps | grep nginx`
- Check logs: `docker logs platform-nginx-prod`
- Try port 8080: `http://YOUR_IP:8080/admin`

### Login page not loading
- Clear browser cache
- Check cosmic-app containers: `docker ps | grep cosmic-app`
- Check logs: `docker logs cosmic-app-1-prod`

### "Too many login attempts"
- Wait 5 minutes
- Or reset throttle: `docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan cache:clear`
