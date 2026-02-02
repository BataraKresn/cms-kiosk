<?php

namespace App\Console\Commands;

use App\Models\Remote;
use App\Services\DeviceHeartbeatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * DeviceStatusMonitorCommand
 * 
 * Runs every minute via Laravel scheduler to:
 * - Enforce server-side timeout rules
 * - Mark devices TEMPORARILY_OFFLINE or OFFLINE based on last_seen_at
 * - Ensure CMS is authoritative source of truth
 * - Independent of external Python service
 */
class DeviceStatusMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:monitor-status
                            {--dry-run : Show what would change without applying}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor device status and enforce timeout rules';

    /**
     * Execute the console command.
     */
    public function handle(DeviceHeartbeatService $heartbeatService)
    {
        $dryRun = $this->option('dry-run');
        $verbose = $this->option('detailed') || $this->option('verbose');
        
        $this->info('Starting device status monitoring...');
        
        // Get all non-deleted devices
        $devices = Remote::whereNull('deleted_at')->get();
        
        $this->info(sprintf('Checking %d devices...', $devices->count()));
        
        $stats = [
            'checked' => 0,
            'updated' => 0,
            'errors' => 0,
            'transitions' => [],
        ];
        
        foreach ($devices as $device) {
            $stats['checked']++;
            
            try {
                if ($dryRun) {
                    // Dry run - just check what would change
                    $this->checkDeviceStatus($device, $stats, $verbose);
                } else {
                    // Actually enforce timeout rules
                    $changed = $heartbeatService->enforceTimeoutRules($device);
                    
                    if ($changed) {
                        $stats['updated']++;
                        
                        // Refresh device to get new status
                        $device->refresh();
                        
                        $transitionKey = sprintf(
                            '%s -> %s',
                            $device->previous_status ?? 'Unknown',
                            $device->status
                        );
                        
                        if (!isset($stats['transitions'][$transitionKey])) {
                            $stats['transitions'][$transitionKey] = 0;
                        }
                        $stats['transitions'][$transitionKey]++;
                        
                        if ($verbose) {
                            $this->line(sprintf(
                                '  [UPDATED] %s (ID: %d): %s -> %s - %s',
                                $device->name,
                                $device->id,
                                $device->previous_status ?? 'Unknown',
                                $device->status,
                                $device->status_change_reason ?? 'No reason'
                            ));
                        }
                    }
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                
                Log::error('Device status monitoring failed', [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'error' => $e->getMessage(),
                ]);
                
                if ($verbose) {
                    $this->error(sprintf(
                        '  [ERROR] %s (ID: %d): %s',
                        $device->name,
                        $device->id,
                        $e->getMessage()
                    ));
                }
            }
        }
        
        // Display summary
        $this->newLine();
        $this->info('=== Device Status Monitoring Summary ===');
        $this->line(sprintf('Devices checked: %d', $stats['checked']));
        $this->line(sprintf('Devices updated: %d', $stats['updated']));
        
        if (count($stats['transitions']) > 0) {
            $this->line('Status transitions:');
            foreach ($stats['transitions'] as $transition => $count) {
                $this->line(sprintf('  - %s: %d device(s)', $transition, $count));
            }
        }
        
        if ($stats['errors'] > 0) {
            $this->warn(sprintf('Errors encountered: %d', $stats['errors']));
        }
        
        if ($dryRun) {
            $this->warn('[DRY RUN] No changes were applied');
        }
        
        Log::info('Device status monitoring completed', [
            'checked' => $stats['checked'],
            'updated' => $stats['updated'],
            'errors' => $stats['errors'],
            'transitions' => $stats['transitions'],
            'dry_run' => $dryRun,
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * Check device status in dry-run mode
     *
     * @param Remote $device
     * @param array &$stats
     * @param bool $verbose
     */
    private function checkDeviceStatus(Remote $device, array &$stats, bool $verbose): void
    {
        if (!$device->last_seen_at) {
            return;
        }
        
        $now = now();
        $lastSeenAge = $now->diffInSeconds($device->last_seen_at);
        
        $heartbeatInterval = $device->heartbeat_interval_seconds ?? DeviceHeartbeatService::DEFAULT_HEARTBEAT_INTERVAL;
        $gracePeriod = $device->grace_period_seconds ?? DeviceHeartbeatService::DEFAULT_GRACE_PERIOD;
        
        $currentStatus = $device->status;
        $projectedStatus = null;
        
        // Determine what status should be
        if ($lastSeenAge < $heartbeatInterval + 10) {
            $projectedStatus = DeviceHeartbeatService::STATUS_CONNECTED;
        } elseif ($lastSeenAge < $gracePeriod) {
            $projectedStatus = DeviceHeartbeatService::STATUS_TEMPORARILY_OFFLINE;
        } elseif ($lastSeenAge < DeviceHeartbeatService::OFFLINE_THRESHOLD) {
            $projectedStatus = DeviceHeartbeatService::STATUS_TEMPORARILY_OFFLINE;
        } else {
            $projectedStatus = DeviceHeartbeatService::STATUS_DISCONNECTED;
        }
        
        if ($currentStatus !== $projectedStatus) {
            $stats['updated']++;
            
            $transitionKey = sprintf('%s -> %s', $currentStatus, $projectedStatus);
            if (!isset($stats['transitions'][$transitionKey])) {
                $stats['transitions'][$transitionKey] = 0;
            }
            $stats['transitions'][$transitionKey]++;
            
            if ($verbose) {
                $this->line(sprintf(
                    '  [WOULD UPDATE] %s (ID: %d): %s -> %s (last seen: %ds ago)',
                    $device->name,
                    $device->id,
                    $currentStatus,
                    $projectedStatus,
                    $lastSeenAge
                ));
            }
        } elseif ($verbose) {
            $this->line(sprintf(
                '  [OK] %s (ID: %d): %s (last seen: %ds ago)',
                $device->name,
                $device->id,
                $currentStatus,
                $lastSeenAge
            ));
        }
    }
}
