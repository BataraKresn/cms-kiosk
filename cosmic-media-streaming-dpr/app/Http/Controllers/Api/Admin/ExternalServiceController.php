<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Remote;
use App\Services\DeviceHeartbeatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ExternalServiceController
 * 
 * Admin-only endpoints for external monitoring services (e.g., Python ping service)
 * to coordinate with CMS device state management.
 * 
 * These endpoints ensure external services respect the CMS as authority
 * and don't cause race conditions or status flapping.
 */
class ExternalServiceController extends Controller
{
    protected DeviceHeartbeatService $heartbeatService;
    
    public function __construct(DeviceHeartbeatService $heartbeatService)
    {
        $this->heartbeatService = $heartbeatService;
    }
    
    /**
     * Process external ping result for a device
     * 
     * POST /api/admin/external-service/device/{id}/ping
     * 
     * Body: {
     *   "ping_successful": true,
     *   "ping_status": "HTTP 200 OK",
     *   "response_time_ms": 45,
     *   "ip_address": "192.168.1.100",
     *   "port": 8080
     * }
     */
    public function processPing(Request $request, int $id)
    {
        $validated = $request->validate([
            'ping_successful' => 'required|boolean',
            'ping_status' => 'required|string|max:255',
            'response_time_ms' => 'nullable|integer',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
        ]);
        
        $remote = Remote::find($id);
        
        if (!$remote) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }
        
        try {
            // Process ping through service (respects heartbeat priority)
            $statusChanged = $this->heartbeatService->processExternalPing(
                $id,
                $validated['ping_successful'],
                $validated['ping_status']
            );
            
            Log::info('External service ping processed', [
                'device_id' => $id,
                'device_name' => $remote->name,
                'ping_successful' => $validated['ping_successful'],
                'status_changed' => $statusChanged,
                'response_time_ms' => $validated['response_time_ms'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'device_id' => $id,
                    'status_changed' => $statusChanged,
                    'current_status' => $remote->fresh()->status,
                    'message' => $statusChanged 
                        ? 'Device status updated' 
                        : 'No status change (recent heartbeat exists or no change needed)',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('External service ping processing failed', [
                'device_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ping processing failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Batch process multiple ping results
     * 
     * POST /api/admin/external-service/devices/ping-batch
     * 
     * Body: {
     *   "results": [
     *     {"device_id": 1, "ping_successful": true, "ping_status": "HTTP 200 OK"},
     *     {"device_id": 2, "ping_successful": false, "ping_status": "Timeout"}
     *   ]
     * }
     */
    public function processPingBatch(Request $request)
    {
        $validated = $request->validate([
            'results' => 'required|array|min:1|max:1000',
            'results.*.device_id' => 'required|integer',
            'results.*.ping_successful' => 'required|boolean',
            'results.*.ping_status' => 'required|string|max:255',
        ]);
        
        $processed = 0;
        $statusChanges = 0;
        $errors = [];
        
        foreach ($validated['results'] as $result) {
            try {
                $statusChanged = $this->heartbeatService->processExternalPing(
                    $result['device_id'],
                    $result['ping_successful'],
                    $result['ping_status']
                );
                
                $processed++;
                if ($statusChanged) {
                    $statusChanges++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'device_id' => $result['device_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        Log::info('External service batch ping processed', [
            'total_submitted' => count($validated['results']),
            'processed' => $processed,
            'status_changes' => $statusChanges,
            'errors' => count($errors),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_submitted' => count($validated['results']),
                'processed' => $processed,
                'status_changes' => $statusChanges,
                'errors' => $errors,
            ]
        ]);
    }
    
    /**
     * Get devices that need external ping
     * Returns devices that haven't been pinged recently by external service
     * 
     * GET /api/admin/external-service/devices/needs-ping
     */
    public function getDevicesNeedingPing(Request $request)
    {
        $intervalSeconds = $request->input('interval_seconds', 30); // Default: 30s
        
        $devices = Remote::whereNull('deleted_at')
            ->where(function ($query) use ($intervalSeconds) {
                $query->whereNull('last_external_ping_at')
                    ->orWhere('last_external_ping_at', '<', now()->subSeconds($intervalSeconds));
            })
            ->select([
                'id',
                'name',
                'ip_address',
                'remote_control_port',
                'status',
                'last_seen_at',
                'last_external_ping_at',
                'last_heartbeat_received_at',
            ])
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $devices->count(),
                'devices' => $devices->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'name' => $device->name,
                        'ip_address' => $device->ip_address,
                        'port' => $device->remote_control_port ?? 8080,
                        'ping_url' => sprintf(
                            'http://%s:%d/ping',
                            $device->ip_address,
                            $device->remote_control_port ?? 8080
                        ),
                        'current_status' => $device->status,
                        'last_heartbeat_at' => $device->last_heartbeat_received_at?->toIso8601String(),
                        'last_external_ping_at' => $device->last_external_ping_at?->toIso8601String(),
                    ];
                }),
            ]
        ]);
    }
}
