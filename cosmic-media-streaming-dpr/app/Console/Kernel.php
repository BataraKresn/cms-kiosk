<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        // Commands\DemoCron::class, // Disabled - not used
    ];

    protected function schedule(Schedule $schedule)
    {
        // REPLACED: Old auto-disconnect logic with new comprehensive device status monitoring
        // New system uses DeviceHeartbeatService for:
        // - Atomic state transitions with row-level locking
        // - Three-tier status: Connected -> Temporarily Offline -> Disconnected
        // - Grace period enforcement (60s default)
        // - Structured logging for debugging status flapping
        // - Coordination with external ping service
        $schedule->command('devices:monitor-status')
            ->everyMinute()
            ->name('device-status-monitor')
            ->withoutOverlapping()
            ->runInBackground();
    }
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
