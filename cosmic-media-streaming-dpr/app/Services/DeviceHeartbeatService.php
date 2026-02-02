<?php

namespace App\Services;

use App\Models\Remote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * DeviceHeartbeatService
 * 
 * Handles all device heartbeat operations with:
 * - Atomic state transitions
 * - Race condition prevention via row-level locking
 * - Grace period enforcement (ONLINE -> TEMPORARILY_OFFLINE -> OFFLINE)
 * - Structured logging for debugging
 * - External service coordination
 */
class DeviceHeartbeatService
{
    // Status constants
    const STATUS_CONNECTED = 'Connected';
    const STATUS_TEMPORARILY_OFFLINE = 'Temporarily Offline';
    const STATUS_DISCONNECTED = 'Disconnected';
    
    // Source constants
    const SOURCE_DEVICE = 'device';
    const SOURCE_EXTERNAL = 'external_service';
    const SOURCE_SYSTEM = 'system';
    
    // Default timing (can be overridden per device)
    const DEFAULT_HEARTBEAT_INTERVAL = 30; // seconds
    const DEFAULT_GRACE_PERIOD = 60; // seconds (2x heartbeat)
    const OFFLINE_THRESHOLD = 300; // 5 minutes - permanent offline
    
    /**
     * Process a device heartbeat with atomic state management
     *
     * @param Remote $remote
     * @param array $metrics Optional device metrics
     * @return array Updated device state
     */
    public function processHeartbeat(Remote $remote, array $metrics = []): array
    {
        return DB::transaction(function () use ($remote, $metrics) {
            // Lock the row to prevent race conditions with external service
            $lockedRemote = Remote::where('id', $remote->id)
                ->lockForUpdate()
                ->first();
            
            if (!$lockedRemote) {
                throw new \Exception("Device not found during heartbeat processing");
            }
            
            $now = Carbon::now();
            $previousStatus = $lockedRemote->status;
            $newStatus = self::STATUS_CONNECTED;
            
            // Build update data
            $updateData = [
                'status' => $newStatus,
                'last_seen_at' => $now,
                'last_heartbeat_received_at' => $now,
                'last_heartbeat_source' => self::SOURCE_DEVICE,
            ];
            
            // Track status change if it changed
            if ($previousStatus !== $newStatus) {
                $updateData['previous_status'] = $previousStatus;
                $updateData['last_status_change_at'] = $now;
                $updateData['status_change_reason'] = sprintf(
                    'Heartbeat received from device (was: %s)',
                    $previousStatus
                );
                
                $this->logStatusTransition(
                    $lockedRemote->id,
                    $lockedRemote->name,
                    $previousStatus,
                    $newStatus,
                    'Heartbeat received',
                    self::SOURCE_DEVICE
                );
            }
            
            // Update optional metrics if provided
            if (isset($metrics['battery_level'])) {
                $updateData['battery_level'] = $metrics['battery_level'];
            }
            if (isset($metrics['wifi_strength'])) {
                $updateData['wifi_strength'] = $metrics['wifi_strength'];
            }
            if (isset($metrics['screen_on'])) {
                $updateData['screen_on'] = $metrics['screen_on'];
            }
            if (isset($metrics['storage_available_mb'])) {
                $updateData['storage_available_mb'] = $metrics['storage_available_mb'];
            }
            if (isset($metrics['storage_total_mb'])) {
                $updateData['storage_total_mb'] = $metrics['storage_total_mb'];
            }
            if (isset($metrics['ram_usage_mb'])) {
                $updateData['ram_usage_mb'] = $metrics['ram_usage_mb'];
            }
            if (isset($metrics['ram_total_mb'])) {
                $updateData['ram_total_mb'] = $metrics['ram_total_mb'];
            }
            if (isset($metrics['cpu_temp'])) {
                $updateData['cpu_temp'] = $metrics['cpu_temp'];
            }
            if (isset($metrics['network_type'])) {
                $updateData['network_type'] = $metrics['network_type'];
            }
            if (isset($metrics['current_url'])) {
                $updateData['current_url'] = $metrics['current_url'];
            }
            
            // Ensure should_reconnect stays true (allow heartbeat by default)
            // Only set to false if explicitly blocked by admin
            if (!isset($updateData['should_reconnect'])) {
                $updateData['should_reconnect'] = true;
            }
            
            // Perform update
            $lockedRemote->update($updateData);
            
            // Refresh to get latest values
            $lockedRemote->refresh();
            
            // Invalidate only THIS device's cache (not global)
            $this->invalidateDeviceCache($lockedRemote);
            
            return [
                'status' => $newStatus,
                'previous_status' => $previousStatus,
                'should_reconnect' => (bool) $lockedRemote->should_reconnect,
                'reconnect_delay_seconds' => $lockedRemote->reconnect_delay_seconds,
                'remote_control_enabled' => (bool) $lockedRemote->remote_control_enabled,
            ];
        });
    }
    
    /**
     * Process external service ping result
     * 
     * This should ONLY update if heartbeat data is stale
     *
     * @param int $remoteId
     * @param bool $pingSuccessful
     * @param string $pingStatus
     * @return bool Whether update was applied
     */
    public function processExternalPing(int $remoteId, bool $pingSuccessful, string $pingStatus): bool
    {
        return DB::transaction(function () use ($remoteId, $pingSuccessful, $pingStatus) {
            $lockedRemote = Remote::where('id', $remoteId)
                ->lockForUpdate()
                ->first();
            
            if (!$lockedRemote) {
                return false;
            }
            
            $now = Carbon::now();
            $previousStatus = $lockedRemote->status;
            
            // External service result
            $updateData = [
                'last_external_ping_at' => $now,
                'external_ping_status' => $pingStatus,
            ];
            
            // CRITICAL: External service must NOT override recent heartbeat
            $heartbeatAge = $lockedRemote->last_heartbeat_received_at
                ? $now->diffInSeconds($lockedRemote->last_heartbeat_received_at)
                : 9999;
            
            $heartbeatInterval = $lockedRemote->heartbeat_interval_seconds ?? self::DEFAULT_HEARTBEAT_INTERVAL;
            
            // If heartbeat is recent (within interval), IGNORE external service
            if ($heartbeatAge < $heartbeatInterval) {
                Log::debug('External ping ignored - recent heartbeat exists', [
                    'device_id' => $remoteId,
                    'heartbeat_age_seconds' => $heartbeatAge,
                    'heartbeat_interval' => $heartbeatInterval,
                    'external_ping_result' => $pingSuccessful ? 'success' : 'failed',
                ]);
                
                $lockedRemote->update($updateData);
                return false; // No status change
            }
            
            // Heartbeat is stale - external service can update status
            if ($pingSuccessful) {
                // External ping succeeded, mark connected
                $newStatus = self::STATUS_CONNECTED;
                $updateData['status'] = $newStatus;
                $updateData['last_seen_at'] = $now;
                $updateData['last_heartbeat_source'] = self::SOURCE_EXTERNAL;
                
                if ($previousStatus !== $newStatus) {
                    $updateData['previous_status'] = $previousStatus;
                    $updateData['last_status_change_at'] = $now;
                    $updateData['status_change_reason'] = 'External service ping successful';
                    
                    $this->logStatusTransition(
                        $remoteId,
                        $lockedRemote->name,
                        $previousStatus,
                        $newStatus,
                        'External ping successful (no recent heartbeat)',
                        self::SOURCE_EXTERNAL
                    );
                }
            } else {
                // External ping failed
                // Determine new status based on grace period
                $gracePeriod = $lockedRemote->grace_period_seconds ?? self::DEFAULT_GRACE_PERIOD;
                $lastSeenAge = $lockedRemote->last_seen_at
                    ? $now->diffInSeconds($lockedRemote->last_seen_at)
                    : 9999;
                
                if ($lastSeenAge < $gracePeriod) {
                    $newStatus = self::STATUS_TEMPORARILY_OFFLINE;
                } elseif ($lastSeenAge < self::OFFLINE_THRESHOLD) {
                    $newStatus = self::STATUS_TEMPORARILY_OFFLINE;
                } else {
                    $newStatus = self::STATUS_DISCONNECTED;
                }
                
                if ($previousStatus !== $newStatus) {
                    $updateData['status'] = $newStatus;
                    $updateData['previous_status'] = $previousStatus;
                    $updateData['last_status_change_at'] = $now;
                    $updateData['last_heartbeat_source'] = self::SOURCE_EXTERNAL;
                    $updateData['status_change_reason'] = sprintf(
                        'External ping failed after %ds (grace: %ds)',
                        $lastSeenAge,
                        $gracePeriod
                    );
                    
                    $this->logStatusTransition(
                        $remoteId,
                        $lockedRemote->name,
                        $previousStatus,
                        $newStatus,
                        'External ping failed',
                        self::SOURCE_EXTERNAL
                    );
                }
            }
            
            $lockedRemote->update($updateData);
            $this->invalidateDeviceCache($lockedRemote);
            
            return isset($updateData['status']);
        });
    }
    
    /**
     * Check and update device status based on last_seen_at timestamp
     * Called by scheduled command every minute
     *
     * @param Remote $remote
     * @return bool Whether status was changed
     */
    public function enforceTimeoutRules(Remote $remote): bool
    {
        return DB::transaction(function () use ($remote) {
            $lockedRemote = Remote::where('id', $remote->id)
                ->lockForUpdate()
                ->first();
            
            if (!$lockedRemote || !$lockedRemote->last_seen_at) {
                return false;
            }
            
            $now = Carbon::now();
            $lastSeenAge = $now->diffInSeconds($lockedRemote->last_seen_at);
            $previousStatus = $lockedRemote->status;
            
            $heartbeatInterval = $lockedRemote->heartbeat_interval_seconds ?? self::DEFAULT_HEARTBEAT_INTERVAL;
            $gracePeriod = $lockedRemote->grace_period_seconds ?? self::DEFAULT_GRACE_PERIOD;
            
            $newStatus = null;
            $reason = null;
            
            // Determine status based on time thresholds
            if ($lastSeenAge < $heartbeatInterval + 10) {
                // Within expected interval + 10s buffer
                $newStatus = self::STATUS_CONNECTED;
            } elseif ($lastSeenAge < $gracePeriod) {
                // Missing heartbeat but within grace period
                $newStatus = self::STATUS_TEMPORARILY_OFFLINE;
                $reason = sprintf(
                    'No heartbeat for %ds (grace period: %ds)',
                    $lastSeenAge,
                    $gracePeriod
                );
            } elseif ($lastSeenAge < self::OFFLINE_THRESHOLD) {
                // Beyond grace period but not permanent offline yet
                $newStatus = self::STATUS_TEMPORARILY_OFFLINE;
                $reason = sprintf(
                    'No heartbeat for %ds (exceeds grace period)',
                    $lastSeenAge
                );
            } else {
                // Permanently offline
                $newStatus = self::STATUS_DISCONNECTED;
                $reason = sprintf(
                    'No heartbeat for %ds (exceeds offline threshold: %ds)',
                    $lastSeenAge,
                    self::OFFLINE_THRESHOLD
                );
            }
            
            // Only update if status actually changed
            if ($previousStatus !== $newStatus) {
                $lockedRemote->update([
                    'status' => $newStatus,
                    'previous_status' => $previousStatus,
                    'last_status_change_at' => $now,
                    'status_change_reason' => $reason ?? 'Server-enforced timeout rule',
                    'last_heartbeat_source' => self::SOURCE_SYSTEM,
                ]);
                
                $this->logStatusTransition(
                    $lockedRemote->id,
                    $lockedRemote->name,
                    $previousStatus,
                    $newStatus,
                    $reason ?? 'Server-enforced timeout',
                    self::SOURCE_SYSTEM
                );
                
                $this->invalidateDeviceCache($lockedRemote);
                
                return true;
            }
            
            return false;
        });
    }
    
    /**
     * Signal device to reconnect (server-initiated)
     *
     * @param Remote $remote
     * @param int|null $delaySeconds Delay before reconnection (null = immediate)
     * @param string|null $reason Reason for reconnection
     */
    public function requestReconnection(Remote $remote, ?int $delaySeconds = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($remote, $delaySeconds, $reason) {
            $lockedRemote = Remote::where('id', $remote->id)
                ->lockForUpdate()
                ->first();
            
            if (!$lockedRemote) {
                throw new \Exception("Device not found");
            }
            
            $lockedRemote->update([
                'should_reconnect' => true,
                'reconnect_delay_seconds' => $delaySeconds,
                'reconnect_reason' => $reason ?? 'Server-initiated reconnection',
            ]);
            
            Log::info('Reconnection requested for device', [
                'device_id' => $lockedRemote->id,
                'device_name' => $lockedRemote->name,
                'delay_seconds' => $delaySeconds,
                'reason' => $reason,
            ]);
            
            $this->invalidateDeviceCache($lockedRemote);
        });
    }
    
    /**
     * Invalidate cache only for this specific device (not global)
     *
     * @param Remote $remote
     */
    private function invalidateDeviceCache(Remote $remote): void
    {
        // Specific device caches only
        Cache::forget('device_token_' . $remote->token);
        Cache::forget('device_rc_status_' . $remote->id);
        Cache::forget('device_status_' . $remote->id);
        Cache::forget('display_content_' . $remote->token);
        
        // DO NOT flush global tags - this prevents cache thrashing
        // Cache::tags(['device_status'])->flush(); // REMOVED
    }
    
    /**
     * Log status transition for debugging and audit
     *
     * @param int $deviceId
     * @param string $deviceName
     * @param string $fromStatus
     * @param string $toStatus
     * @param string $reason
     * @param string $source
     */
    private function logStatusTransition(
        int $deviceId,
        string $deviceName,
        string $fromStatus,
        string $toStatus,
        string $reason,
        string $source
    ): void {
        Log::info('Device status transition', [
            'device_id' => $deviceId,
            'device_name' => $deviceName,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'source' => $source,
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }
}
