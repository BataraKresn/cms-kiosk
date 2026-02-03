<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Remote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DeviceRegistrationController extends Controller
{
    /**
     * Register new Android device automatically
     * Called by APK on first launch
     * 
     * POST /api/devices/register
     * Body: {
     *   "device_name": "KIOSK-DEPAN-ESKALATOR",
     *   "device_id": "android-unique-id",
     *   "mac_address": "00:11:22:33:44:55",
     *   "android_version": "11",
     *   "app_version": "1.0.0",
     *   "ip_address": "100.101.102.103"
     * }
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required|string|max:255',
            'device_id' => 'required|string|max:255',
            'mac_address' => 'nullable|string|max:17',
            'android_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
            'ip_address' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 200);
        }

        $deviceId = $request->device_id;
        
        // Check if device already registered
        $remote = Remote::where('device_identifier', $deviceId)
            ->whereNull('deleted_at')
            ->first();

        if ($remote) {
            // Device exists, update info and return existing token
            $remote->update([
                'name' => $request->device_name,
                'ip_address' => $request->ip_address,
                'mac_address' => $request->mac_address,
                'android_version' => $request->android_version,
                'app_version' => $request->app_version,
                'last_seen_at' => now(),
                'status' => 'Connected',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully',
                'data' => [
                    'remote_id' => $remote->id,
                    'token' => $remote->token,
                    'remote_control_enabled' => (bool) $remote->remote_control_enabled,
                    'websocket_url' => config('remotecontrol.relay_ws_url'),
                ]
            ], 200);
        }

        // Create new remote record
        $token = Str::random(64);
        
        $remote = Remote::create([
            'name' => $request->device_name,
            'device_identifier' => $deviceId,
            'token' => $token,
            'ip_address' => $request->ip_address,
            'mac_address' => $request->mac_address,
            'android_version' => $request->android_version,
            'app_version' => $request->app_version,
            'status' => 'Connected',
            'last_seen_at' => now(),
            'remote_control_enabled' => 0, // Disabled by default, admin must enable
            'remote_control_port' => 5555,
            'should_reconnect' => true, // Allow heartbeat by default
            'heartbeat_interval_seconds' => 30,
            'grace_period_seconds' => 300,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'data' => [
                'remote_id' => $remote->id,
                'token' => $token,
                'remote_control_enabled' => false,
                'websocket_url' => config('remotecontrol.relay_ws_url'),
            ]
        ], 200);
    }

    /**
     * Device heartbeat to keep status alive
     * Called by APK every 30 seconds
     * 
     * POST /api/devices/heartbeat
     * Headers: Authorization: Bearer {token}
     * Body: {
     *   "status": "online",
     *   "battery_level": 85,
     *   "wifi_strength": -45,
     *   "screen_on": true,
     *   "storage_available_mb": 15360,
     *   "storage_total_mb": 32768,
     *   "ram_usage_mb": 2048,
     *   "ram_total_mb": 4096,
     *   "cpu_temp": 42.5,
     *   "network_type": "WiFi",
     *   "current_url": "https://kiosk.mugshot.dev/display/abc123"
     * }
     */
    public function heartbeat(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token required'
            ], 401);
        }

        $remote = Remote::where('token', $token)
            ->whereNull('deleted_at')
            ->first();

        if (!$remote) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        // Extract device metrics
        $metrics = [];
        if ($request->has('battery_level')) {
            $metrics['battery_level'] = $request->battery_level;
        }
        if ($request->has('wifi_strength')) {
            $metrics['wifi_strength'] = $request->wifi_strength;
        }
        if ($request->has('screen_on')) {
            $metrics['screen_on'] = $request->boolean('screen_on');
        }
        if ($request->has('storage_available_mb')) {
            $metrics['storage_available_mb'] = $request->storage_available_mb;
        }
        if ($request->has('storage_total_mb')) {
            $metrics['storage_total_mb'] = $request->storage_total_mb;
        }
        if ($request->has('ram_usage_mb')) {
            $metrics['ram_usage_mb'] = $request->ram_usage_mb;
        }
        if ($request->has('ram_total_mb')) {
            $metrics['ram_total_mb'] = $request->ram_total_mb;
        }
        if ($request->has('cpu_temp')) {
            $metrics['cpu_temp'] = $request->cpu_temp;
        }
        if ($request->has('network_type')) {
            $metrics['network_type'] = $request->network_type;
        }
        if ($request->has('current_url')) {
            $metrics['current_url'] = $request->current_url;
        }

        // Use DeviceHeartbeatService for atomic state management
        try {
            $heartbeatService = app(\App\Services\DeviceHeartbeatService::class);
            $result = $heartbeatService->processHeartbeat($remote, $metrics);
            
            Log::info('Heartbeat processed successfully', [
                'device_id' => $remote->id,
                'device_name' => $remote->name,
                'status' => $result['status'],
                'previous_status' => $result['previous_status'],
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'remote_control_enabled' => $result['remote_control_enabled'],
                    'should_reconnect' => $result['should_reconnect'],
                    'reconnect_delay_seconds' => $result['reconnect_delay_seconds'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Heartbeat processing failed', [
                'device_id' => $remote->id,
                'device_name' => $remote->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Heartbeat processing failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Unregister device (when APK uninstalled or reset)
     * 
     * DELETE /api/devices/unregister
     * Headers: Authorization: Bearer {token}
     */
    public function unregister(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token required'
            ], 401);
        }

        $remote = Remote::where('token', $token)
            ->whereNull('deleted_at')
            ->first();

        if (!$remote) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        // Soft delete
        $remote->update([
            'status' => 'Disconnected',
            'deleted_at' => now(),
        ]);

        Log::info('Device unregistered', [
            'remote_id' => $remote->id,
            'device_name' => $remote->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device unregistered successfully'
        ]);
    }

    /**
     * Get list of available displays for APK selection
     * Called by APK Settings screen to populate display dropdown
     * 
     * GET /api/displays?search=...&per_page=50
     * Returns: {
     *   "data": [
     *     {"id": 1, "name": "Depan Perpustakaan", "token": "abc123", "created_at": "2026-01-31..."},
     *     ...
     *   ],
     *   "total": 10
     * }
     */
    public function getDisplays(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');

        $query = \App\Models\Display::query();

        // Apply search filter if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('token', 'like', "%{$search}%");
            });
        }

        // Order by name ascending
        $query->orderBy('name', 'asc');

        // Paginate results
        $displays = $query->paginate($perPage);

        // Transform to simple format for APK
        $data = $displays->map(function($display) {
            return [
                'id' => $display->id,
                'name' => $display->name,
                'token' => $display->token,
                'created_at' => $display->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $displays->total(),
            'per_page' => $displays->perPage(),
            'current_page' => $displays->currentPage(),
            'last_page' => $displays->lastPage(),
        ]);
    }
}
