<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupRemoteSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remote:cleanup-sessions
                            {--minutes=10 : Close sessions older than this many minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close orphaned remote control sessions that are still marked as active';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        $threshold = Carbon::now()->subMinutes($minutes);

        $count = DB::table('remote_sessions')
            ->where('status', 'active')
            ->where('started_at', '<', $threshold)
            ->update([
                'status' => 'ended',
                'ended_at' => now(),
                'duration_seconds' => DB::raw('TIMESTAMPDIFF(SECOND, started_at, NOW())')
            ]);

        if ($count > 0) {
            $this->info("✅ Closed {$count} orphaned session(s) older than {$minutes} minutes");
        } else {
            $this->info("ℹ️  No orphaned sessions found");
        }

        return 0;
    }
}
