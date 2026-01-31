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
        // Auto-disconnect devices that haven't sent heartbeat in 5 minutes
        // Extended grace period from 2 to 5 minutes to prevent status flapping
        // APK sends heartbeat every 30 seconds, so 5 minutes = 10 missed heartbeats
        $schedule->call(function () {
            $disconnectedCount = \Illuminate\Support\Facades\DB::table('remotes')
                ->where('status', 'Connected')
                ->where('last_seen_at', '<', now()->subMinutes(5))
                ->update(['status' => 'Disconnected']);
            
            if ($disconnectedCount > 0) {
                // Clear device cache after status update
                \Illuminate\Support\Facades\Cache::tags(['device_status'])->flush();
            }
        })->everyMinute()->name('auto-disconnect-inactive-devices');
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
