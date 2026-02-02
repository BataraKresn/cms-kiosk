<?php

namespace App\Http\Middleware;

use App\Models\Remote;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * HeartbeatRateLimiter
 * 
 * Prevents heartbeat abuse by enforcing minimum interval between heartbeats.
 * Rejects heartbeats that arrive too frequently without marking device offline.
 */
class HeartbeatRateLimiter
{
    /**
     * Minimum seconds between heartbeats (prevents abuse)
     */
    const MIN_HEARTBEAT_INTERVAL = 10; // 10 seconds minimum
    
    /**
     * Maximum heartbeats per minute (prevents DOS)
     */
    const MAX_HEARTBEATS_PER_MINUTE = 10;
    
    /**
     * Handle an incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            // Let the controller handle missing token
            return $next($request);
        }
        
        // Use database row for rate limiting state (no Redis required)
        $remote = DB::transaction(function () use ($token) {
            return Remote::where('token', $token)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();
        });
        
        if (!$remote) {
            // Let the controller handle invalid device
            return $next($request);
        }
        
        $now = Carbon::now();
        $lastHeartbeat = $remote->last_heartbeat_received_at;
        
        // Check minimum interval
        if ($lastHeartbeat) {
            $secondsSinceLastHeartbeat = $now->diffInSeconds($lastHeartbeat);
            
            if ($secondsSinceLastHeartbeat < self::MIN_HEARTBEAT_INTERVAL) {
                Log::warning('Heartbeat rate limit exceeded - too frequent', [
                    'device_id' => $remote->id,
                    'device_name' => $remote->name,
                    'seconds_since_last' => $secondsSinceLastHeartbeat,
                    'min_interval' => self::MIN_HEARTBEAT_INTERVAL,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Heartbeat rate limit exceeded. Minimum interval: ' . self::MIN_HEARTBEAT_INTERVAL . ' seconds.',
                    'retry_after_seconds' => self::MIN_HEARTBEAT_INTERVAL - $secondsSinceLastHeartbeat,
                    'data' => [
                        'remote_control_enabled' => (bool) $remote->remote_control_enabled,
                        'should_reconnect' => (bool) $remote->should_reconnect,
                        'reconnect_delay_seconds' => $remote->reconnect_delay_seconds,
                    ]
                ], 429); // 429 Too Many Requests
            }
        }
        
        // Check per-minute rate limit
        $currentMinute = $now->format('Y-m-d H:i');
        $lastHeartbeatMinute = $lastHeartbeat ? $lastHeartbeat->format('Y-m-d H:i') : null;
        
        if ($currentMinute === $lastHeartbeatMinute) {
            // Same minute - increment counter
            $currentCount = $remote->heartbeat_count_current_minute;
            
            if ($currentCount >= self::MAX_HEARTBEATS_PER_MINUTE) {
                Log::warning('Heartbeat rate limit exceeded - too many per minute', [
                    'device_id' => $remote->id,
                    'device_name' => $remote->name,
                    'count_this_minute' => $currentCount,
                    'max_per_minute' => self::MAX_HEARTBEATS_PER_MINUTE,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Heartbeat rate limit exceeded. Maximum ' . self::MAX_HEARTBEATS_PER_MINUTE . ' heartbeats per minute.',
                    'retry_after_seconds' => 60,
                    'data' => [
                        'remote_control_enabled' => (bool) $remote->remote_control_enabled,
                        'should_reconnect' => (bool) $remote->should_reconnect,
                        'reconnect_delay_seconds' => $remote->reconnect_delay_seconds,
                    ]
                ], 429);
            }
            
            // Increment counter (will be done in controller via service)
            $request->attributes->set('heartbeat_count_increment', true);
        } else {
            // New minute - reset counter (will be done in controller via service)
            $request->attributes->set('heartbeat_count_reset', true);
        }
        
        return $next($request);
    }
}
