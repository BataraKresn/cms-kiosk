# 🎯 Quick Reference: Remote Control Setup

## 1. Migration Fix
```bash
./fix-migrations.sh
```

## 2. Architecture
- **Relay Server**: Middleman (port 3003)
- **Android APK**: Device being controlled
- **CMS Viewer**: Browser UI controlling device

## 3. Login CMS
**URL**: `https://kiosk.mugshot.dev/admin`
**Email**: `administrator@cms.id`
**Password**: (ask team or reset)

## 4. Enable Remote Control on Device
```sql
docker compose -f docker-compose.prod.yml exec mariadb mysql -ukiosk_platform -p5Kh3dY82ry05 platform

UPDATE remotes 
SET remote_control_enabled = 1, 
    status = 'Connected' 
WHERE id = 1;
```

## 5. Build Android APK
```bash
cd /home/ubuntu/kiosk/kiosk-touchscreen-app

# Check env.properties first!
cat env.properties

# Build
./gradlew clean assembleDebug

# APK location:
# app/build/outputs/apk/debug/app-debug.apk
```

## 6. Install & Setup on Android Device
1. Install APK via ADB or manually
2. Open Settings → Accessibility
3. Enable "Cosmic Remote Control"
4. Grant MediaProjection permission when prompted

## 7. Test Connection
```bash
# Check relay server
curl http://localhost:3002/health

# Watch logs
docker logs -f remote-relay-prod

# From CMS: Management → Remotes → Click "Remote Control"
```

## Current Status ✅
- [x] Database migrations: **DONE**
- [x] Relay server: **RUNNING**
- [x] Android code: **INTEGRATED**
- [x] CMS viewer: **DEPLOYED**
- [x] env.properties: **CONFIGURED**

## Next Steps 🚀
1. Build APK
2. Install on kiosk device
3. Enable accessibility
4. Test from CMS admin panel

## Documentation
- Architecture: `/home/ubuntu/kiosk/doc/REMOTE_CONTROL_ARCHITECTURE_EXPLAINED.md`
- CMS Login: `/home/ubuntu/kiosk/doc/CMS_LOGIN_GUIDE.md`
- APK Connection: `/home/ubuntu/kiosk/doc/APK_CONNECTION_GUIDE.md`
- Main POC: `/home/ubuntu/kiosk/doc/REMOTE_CONTROL_POC.md`
