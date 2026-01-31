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
        // $schedule->command('demo:cron')
        //     ->everyMinute();
        
        // Add your scheduled tasks here
        // Example: Auto-disconnect devices that haven't sent heartbeat in 2 minutes
        // $schedule->call(function () {
        //     \App\Models\Remote::where('last_seen_at', '<', now()->subMinutes(2))
        //         ->update(['status' => 'Disconnected']);
        // })->everyMinute();
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
