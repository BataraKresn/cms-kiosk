# 📚 Remote Control Implementation Guide

> **Complete Step-by-Step Guide**  
> From POC to Production-Ready Remote Control System

---

## 🎯 Overview

This guide walks you through implementing the custom Android remote control system from scratch. Follow each phase sequentially for best results.

**Timeline**: 6-8 weeks for production-ready solution  
**Team**: 1-3 developers  
**Complexity**: Intermediate to Advanced

---

## 📋 Prerequisites

### Required Knowledge
- ✅ Android development (Kotlin)
- ✅ Laravel/PHP backend
- ✅ WebSocket concepts
- ✅ Basic networking
- ✅ Database design

### Required Tools
- ✅ Android Studio
- ✅ Node.js 18+ or Python 3.10+
- ✅ Docker & Docker Compose
- ✅ MariaDB/MySQL
- ✅ Git

### Existing Codebase
- ✅ `/home/ubuntu/kiosk/kiosk-touchscreen-dpr-app` - Android APK
- ✅ `/home/ubuntu/kiosk/cosmic-media-streaming-dpr` - Laravel CMS
- ✅ `/home/ubuntu/kiosk/remote-android-device` - Device monitoring service

---

## 🚀 Phase 1: Foundation Setup (Week 1)

### Step 1.1: Database Migrations

**Location**: `cosmic-media-streaming-dpr/database/migrations/`

```bash
cd /home/ubuntu/kiosk/cosmic-media-streaming-dpr

# Copy migration files from POC
cp /home/ubuntu/kiosk/remote-control-poc/migrations/*.php \
   database/migrations/

# Run migrations
docker compose exec cosmic-app php artisan migrate

# Verify tables created
docker compose exec mariadb mysql -uplatform_user -p${DB_PASSWORD} platform \
  -e "SHOW TABLES LIKE 'remote_%';"
```

**Expected Output**:
```
remote_sessions
remote_permissions
remote_recordings
```

### Step 1.2: Update remotes Table

The fourth migration automatically adds fields to `remotes` table:
```sql
-- Verify new fields
SELECT 
  remote_control_enabled,
  screen_resolution,
  capture_service_running
FROM remotes LIMIT 1;
```

### Step 1.3: Seed Initial Permissions

```bash
# Create seeder
php artisan make:seeder RemotePermissionSeeder
```

**File**: `database/seeders/RemotePermissionSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Remote;
use Illuminate\Support\Facades\DB;

class RemotePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Grant admin users full permissions on all devices
        $adminRole = Role::where('name', 'admin')->first();
        $admins = User::whereHas('roles', function($query) use ($adminRole) {
            $query->where('id', $adminRole->id);
        })->get();
        
        foreach ($admins as $admin) {
            DB::table('remote_permissions')->insert([
                'user_id' => $admin->id,
                'remote_id' => null, // NULL = all devices
                'can_view' => true,
                'can_control' => true,
                'can_record' => true,
                'can_adjust_quality' => true,
                'granted_by' => 1, // System
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('✅ Admin permissions created');
    }
}
```

```bash
# Run seeder
php artisan db:seed --class=RemotePermissionSeeder
```

---

## 🛠️ Phase 2: Relay Server Setup (Week 1)

### Step 2.1: Create Relay Server Directory

```bash
cd /home/ubuntu/kiosk
cp -r remote-control-poc/relay-server ./remote-control-relay
cd remote-control-relay
```

### Step 2.2: Install Dependencies

```bash
# Install Node.js packages
npm install

# Create environment file
cp .env.example .env
nano .env
```

**Edit `.env`**:
```env
HTTP_PORT=3002
WS_PORT=3003
DB_HOST=localhost
DB_PORT=3306
DB_USER=platform_user
DB_PASSWORD=your_actual_password
DB_NAME=platform
NODE_ENV=development
```

### Step 2.3: Test Relay Server

```bash
# Run in development mode
npm run dev
```

**Expected Output**:
```
✅ Database connection pool created
✅ Database connection test successful
🌐 HTTP server running on port 3002
🔌 WebSocket server running on port 3003
✅ Remote Control Relay Server started
```

**Test Health Endpoint**:
```bash
curl http://localhost:3002/health
```

### Step 2.4: Add to Docker Compose

**Edit**: `/home/ubuntu/kiosk/docker-compose.prod.yml`

```yaml
  # Add this service
  remote-control-relay:
    build: ./remote-control-relay
    container_name: remote-control-relay
    restart: always
    env_file:
      - .env.prod
    environment:
      - DB_HOST=mariadb
      - DB_PORT=3306
      - DB_USER=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - DB_NAME=${DB_DATABASE}
      - HTTP_PORT=3002
      - WS_PORT=3003
    ports:
      - "3002:3002"
      - "3003:3003"
    depends_on:
      mariadb:
        condition: service_healthy
    networks:
      - kiosk-net
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:3002/health"]
      interval: 30s
      timeout: 10s
      retries: 3
```

---

## 📱 Phase 3: Android APK Integration (Week 2-3)

### Step 3.1: Copy Service Files

```bash
cd /home/ubuntu/kiosk/kiosk-touchscreen-dpr-app

# Create services directory
mkdir -p app/src/main/java/com/kiosktouchscreendpr/cosmic/services

# Copy service files
cp /home/ubuntu/kiosk/remote-control-poc/android/*.kt \
   app/src/main/java/com/kiosktouchscreendpr/cosmic/services/
```

### Step 3.2: Update AndroidManifest.xml

**File**: `app/src/main/AndroidManifest.xml`

Add permissions:
```xml
<manifest>
    <!-- Existing permissions... -->
    
    <!-- Add these new permissions -->
    <uses-permission android:name="android.permission.SYSTEM_ALERT_WINDOW" />
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE_MEDIA_PROJECTION" />
    
    <application>
        <!-- Existing activities/services... -->
        
        <!-- Add ScreenCaptureService -->
        <service
            android:name=".services.ScreenCaptureService"
            android:foregroundServiceType="mediaProjection"
            android:exported="false" />
        
        <!-- Add InputInjectionService -->
        <service
            android:name=".services.InputInjectionService"
            android:permission="android.permission.BIND_ACCESSIBILITY_SERVICE"
            android:exported="true">
            <intent-filter>
                <action android:name="android.accessibilityservice.AccessibilityService" />
            </intent-filter>
            <meta-data
                android:name="android.accessibilityservice"
                android:resource="@xml/accessibility_service_config" />
        </service>
        
    </application>
</manifest>
```

### Step 3.3: Create Accessibility Service Config

**File**: `app/src/main/res/xml/accessibility_service_config.xml`

```xml
<?xml version="1.0" encoding="utf-8"?>
<accessibility-service xmlns:android="http://schemas.android.com/apk/res/android"
    android:accessibilityEventTypes="typeAllMask"
    android:accessibilityFeedbackType="feedbackGeneric"
    android:accessibilityFlags="flagDefault|flagRequestTouchExplorationMode|flagRequestFilterKeyEvents"
    android:canPerformGestures="true"
    android:canRetrieveWindowContent="true"
    android:description="@string/accessibility_service_description"
    android:notificationTimeout="100"
    android:settingsActivity="com.kiosktouchscreendpr.cosmic.presentation.settings.SettingsView" />
```

**Add to strings.xml**:
```xml
<string name="accessibility_service_description">
    Allows remote control of this device via Cosmic CMS. Required for touch event injection.
</string>
```

### Step 3.4: Create Remote Control ViewModel

**File**: `app/src/main/java/.../presentation/remotecontrol/RemoteControlViewModel.kt`

```kotlin
package com.kiosktouchscreendpr.cosmic.presentation.remotecontrol

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.kiosktouchscreendpr.cosmic.services.RemoteControlWebSocketClient
import com.kiosktouchscreendpr.cosmic.services.ScreenCaptureService
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class RemoteControlViewModel @Inject constructor(
    private val wsClient: RemoteControlWebSocketClient
) : ViewModel() {

    private val _state = MutableStateFlow(RemoteControlState())
    val state: StateFlow<RemoteControlState> = _state

    fun startRemoteControl(
        wsUrl: String,
        deviceToken: String,
        deviceId: String
    ) {
        viewModelScope.launch {
            wsClient.connect(wsUrl, deviceToken, deviceId)
            
            wsClient.connectionState.collect { connectionState ->
                _state.value = _state.value.copy(
                    connectionState = connectionState
                )
            }
        }
    }

    fun stopRemoteControl() {
        wsClient.disconnect()
    }

    override fun onCleared() {
        super.onCleared()
        wsClient.shutdown()
    }
}

data class RemoteControlState(
    val connectionState: RemoteControlWebSocketClient.ConnectionState = 
        RemoteControlWebSocketClient.ConnectionState.DISCONNECTED
)
```

### Step 3.5: Add Remote Control Toggle to Settings

**File**: `presentation/settings/SettingsView.kt`

Add toggle switch:
```kotlin
// In Settings UI
Switch(
    checked = remoteControlEnabled,
    onCheckedChange = { enabled ->
        if (enabled) {
            // Request MediaProjection permission
            requestMediaProjectionPermission()
        } else {
            // Stop services
            viewModel.stopRemoteControl()
        }
    },
    label = "Enable Remote Control"
)
```

### Step 3.6: Build and Test APK

```bash
# Build debug APK
./gradlew assembleDebug

# Install on device
adb install -r app/build/outputs/apk/debug/app-debug.apk

# Enable AccessibilityService manually on device:
# Settings > Accessibility > Cosmic Kiosk > Enable
```

---

## 💻 Phase 4: CMS Frontend Integration (Week 3-4)

### Step 4.1: Create Filament Page

**File**: `cosmic-media-streaming-dpr/app/Filament/Resources/RemoteResource/Pages/RemoteControlViewer.php`

```php
<?php

namespace App\Filament\Resources\RemoteResource\Pages;

use App\Filament\Resources\RemoteResource;
use App\Models\Remote;
use App\Models\RemotePermission;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RemoteControlViewer extends Page
{
    protected static string $resource = RemoteResource::class;
    protected static string $view = 'filament.pages.remote-control-viewer';
    protected static ?string $title = 'Remote Control';
    
    public Remote $device;
    public string $deviceStatus = 'disconnected';
    public bool $canControl = false;
    public bool $canRecord = false;
    
    public function mount(int $record): void
    {
        $this->device = Remote::findOrFail($record);
        
        // Check permissions
        $permission = RemotePermission::where('user_id', Auth::id())
            ->where(function($query) use ($record) {
                $query->where('remote_id', $record)
                      ->orWhereNull('remote_id');
            })
            ->first();
        
        if (!$permission || !$permission->can_view) {
            abort(403, 'You do not have permission to view this device');
        }
        
        $this->canControl = $permission->can_control ?? false;
        $this->canRecord = $permission->can_record ?? false;
        
        // Check device status
        $this->deviceStatus = $this->device->status === 'Connected' ? 'connected' : 'disconnected';
    }
}
```

### Step 4.2: Copy Blade Template

```bash
# Copy Blade view
cp /home/ubuntu/kiosk/remote-control-poc/cms-viewer/remote-control-viewer.blade.php \
   cosmic-media-streaming-dpr/resources/views/filament/pages/
```

### Step 4.3: Copy JavaScript File

```bash
# Copy JS to public directory
cp /home/ubuntu/kiosk/remote-control-poc/cms-viewer/remote-control-viewer.js \
   cosmic-media-streaming-dpr/public/js/
```

### Step 4.4: Add Route to Filament Resource

**File**: `app/Filament/Resources/RemoteResource.php`

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListRemotes::route('/'),
        'create' => Pages\CreateRemote::route('/create'),
        'edit' => Pages\EditRemote::route('/{record}/edit'),
        'view' => Pages\ViewRemote::route('/{record}'),
        
        // Add this line
        'control' => Pages\RemoteControlViewer::route('/{record}/control'),
    ];
}
```

### Step 4.5: Add "Remote Control" Button to Table

**File**: `app/Filament/Resources/RemoteResource.php`

```php
use Filament\Tables\Actions\Action;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ... existing columns
        ])
        ->actions([
            // Existing actions...
            
            Action::make('remote_control')
                ->label('Remote Control')
                ->icon('heroicon-o-tv')
                ->color('info')
                ->url(fn (Remote $record): string => 
                    RemoteResource::getUrl('control', ['record' => $record])
                )
                ->visible(fn (Remote $record): bool => 
                    $record->remote_control_enabled && 
                    $record->status === 'Connected'
                )
                ->openUrlInNewTab(),
        ]);
}
```

### Step 4.6: Update Laravel Config

**File**: `cosmic-media-streaming-dpr/config/app.php`

Add to config array:
```php
'remote_control_ws_url' => env('REMOTE_CONTROL_WS_URL', 'ws://localhost:3003'),
```

**File**: `.env`
```env
REMOTE_CONTROL_WS_URL=ws://localhost:3003
```

For production (WSS):
```env
REMOTE_CONTROL_WS_URL=wss://kiosk.mugshot.dev:3003
```

---

## 🧪 Phase 5: Testing & Validation (Week 4)

### Test 1: Database & Permissions

```bash
# Check tables
docker compose exec mariadb mysql -uplatform_user -p platform \
  -e "SELECT * FROM remote_permissions LIMIT 5;"

# Enable remote control for a device
docker compose exec mariadb mysql -uplatform_user -p platform \
  -e "UPDATE remotes SET remote_control_enabled = 1 WHERE id = 1;"
```

### Test 2: Relay Server

```bash
# Terminal 1: Start relay server
cd /home/ubuntu/kiosk/remote-control-relay
npm run dev

# Terminal 2: Test WebSocket connection
npm install -g wscat
wscat -c ws://localhost:3003

# Send auth message
{"type":"auth","role":"device","deviceId":"1","token":"your_device_token"}

# Expected: {"type":"auth_success",...}
```

### Test 3: Android APK

1. Install APK on device
2. Go to Settings > Accessibility > Enable "Cosmic Kiosk"
3. In app settings, enable "Remote Control"
4. Grant MediaProjection permission
5. Check logs: `adb logcat -s RemoteControlWS ScreenCaptureService`

### Test 4: End-to-End

1. Ensure device is connected to network
2. Open CMS: `http://localhost:8000/back-office/remotes`
3. Click "Remote Control" button on a device
4. Should see device screen appear in browser
5. Click on screen → touch should register on device

---

## 🔧 Phase 6: Production Optimizations (Week 5-6)

### 6.1: Enable SSL/WSS

**Nginx Configuration**: `/etc/nginx/sites-available/kiosk.conf`

```nginx
# WebSocket proxy
location /remote-control {
    proxy_pass http://localhost:3003;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 3600s;
}
```

Update `.env`:
```env
REMOTE_CONTROL_WS_URL=wss://kiosk.mugshot.dev/remote-control
```

### 6.2: Performance Tuning

**Android APK** - Adjust settings:
```kotlin
// ScreenCaptureService.kt
private const val TARGET_FPS = 25  // Lower FPS for bandwidth
private const val JPEG_QUALITY = 70  // Lower quality for bandwidth
private const val CAPTURE_WIDTH = 720  // Scale down from 1080
private const val CAPTURE_HEIGHT = 1280  // Scale down from 1920
```

**Relay Server** - Add rate limiting:
```javascript
// In server.js
const FRAME_RATE_LIMIT = 30; // Max frames per second
const lastFrameTime = new Map();

function shouldDropFrame(deviceId) {
    const now = Date.now();
    const last = lastFrameTime.get(deviceId) || 0;
    if (now - last < 1000 / FRAME_RATE_LIMIT) {
        return true; // Drop frame
    }
    lastFrameTime.set(deviceId, now);
    return false;
}
```

### 6.3: Security Hardening

1. **Add CORS restrictions**:
```javascript
// In relay server
const ALLOWED_ORIGINS = [
    'https://kiosk.mugshot.dev',
    'https://cms.mugshot.dev'
];
```

2. **Implement session tokens** (instead of using device token):
```php
// In Laravel
$sessionToken = Str::random(64);
RemoteSession::create([
    'user_id' => auth()->id(),
    'remote_id' => $device->id,
    'session_token' => hash('sha256', $sessionToken),
    'status' => 'active'
]);
```

3. **Add rate limiting** for input commands

### 6.4: Monitoring & Logging

**Add PM2 for Relay Server**:
```bash
pm2 start server.js \
  --name remote-control-relay \
  --log /var/log/remote-control-relay.log \
  --error /var/log/remote-control-relay-error.log

pm2 save
pm2 startup
```

**Laravel Logging**:
```php
// Log remote control sessions
Log::channel('remote_control')->info('Session started', [
    'user' => auth()->id(),
    'device' => $device->id,
    'ip' => request()->ip()
]);
```

---

## 📊 Phase 7: Advanced Features (Week 7-8)

### 7.1: Recording Implementation

See `remote_recordings` table for metadata storage.

**Storage**: Use MinIO for video files.

### 7.2: Multi-Viewer Support

Already implemented in relay server (multiple viewers per device room).

### 7.3: WebRTC Migration

For production, consider migrating to WebRTC for:
- Lower latency
- Adaptive bitrate
- Better quality

---

## 🐛 Troubleshooting

### Issue: "Authentication Failed"

**Cause**: Device token not found or remote_control_enabled = false

**Solution**:
```sql
-- Check device
SELECT id, token, remote_control_enabled FROM remotes WHERE id = 1;

-- Enable remote control
UPDATE remotes SET remote_control_enabled = 1 WHERE id = 1;
```

### Issue: "No frames received"

**Checklist**:
1. ScreenCaptureService running? Check `adb logcat`
2. MediaProjection permission granted?
3. WebSocket connected? Check relay server logs
4. Firewall blocking port 3003?

### Issue: "Touch not working"

**Checklist**:
1. AccessibilityService enabled? Settings > Accessibility
2. User has `can_control` permission?
3. Input commands reaching device? Check Android logs

---

## ✅ Completion Checklist

- [ ] Database migrations run successfully
- [ ] Relay server running and accessible
- [ ] Android services integrated into APK
- [ ] AccessibilityService configuration added
- [ ] APK builds without errors
- [ ] CMS viewer page displays
- [ ] WebSocket connection establishes
- [ ] Video frames display in browser
- [ ] Touch events work end-to-end
- [ ] Swipe gestures work
- [ ] Keyboard input works
- [ ] Permissions system enforced
- [ ] Session tracking functional
- [ ] SSL/WSS enabled for production
- [ ] Monitoring and logging configured

---

## 📚 Additional Resources

- [Android MediaProjection Documentation](https://developer.android.com/reference/android/media/projection/MediaProjection)
- [AccessibilityService Guide](https://developer.android.com/guide/topics/ui/accessibility/service)
- [WebSocket Protocol RFC](https://datatracker.ietf.org/doc/html/rfc6455)
- [Filament PHP Documentation](https://filamentphp.com/docs)

---

## 🤝 Support

For issues or questions:
1. Check logs: Android logcat, relay server logs, Laravel logs
2. Review this guide thoroughly
3. Check POC documentation: `/doc/REMOTE_CONTROL_POC.md`
4. Contact development team

---

**Last Updated**: January 28, 2026  
**Version**: 1.0.0  
**Status**: ✅ Production Ready
