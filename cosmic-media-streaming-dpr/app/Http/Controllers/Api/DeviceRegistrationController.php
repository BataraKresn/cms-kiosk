<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Remote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
                'errors' => $validator->errors()
            ], 422);
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

            Log::info('Device re-registered', [
                'device_id' => $deviceId,
                'remote_id' => $remote->id
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
            ]);
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
        ]);

        Log::info('New device registered', [
            'device_id' => $deviceId,
            'remote_id' => $remote->id,
            'device_name' => $request->device_name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'data' => [
                'remote_id' => $remote->id,
                'token' => $token,
                'remote_control_enabled' => false,
                'websocket_url' => config('remotecontrol.relay_ws_url'),
                'instructions' => 'Device registered. Admin must enable remote control in CMS to activate this feature.'
            ]
        ], 201);
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
     *   "wifi_strength": -45
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

        // Update last seen and status
        $remote->update([
            'last_seen_at' => now(),
            'status' => 'Connected',
            'battery_level' => $request->battery_level,
            'wifi_strength' => $request->wifi_strength,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'remote_control_enabled' => (bool) $remote->remote_control_enabled,
                'should_reconnect' => false,
            ]
        ]);
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
}
