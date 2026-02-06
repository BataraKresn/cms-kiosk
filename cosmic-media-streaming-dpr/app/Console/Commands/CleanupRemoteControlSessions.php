<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupRemoteControlSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remote-control:cleanup-session-logs 
                            {--inactive-minutes=10 : Minutes of inactivity before cleanup}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old/orphaned remote control session logs (SAFE: only removes old logs, never removes devices)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inactiveMinutes = (int) $this->option('inactive-minutes');
        $force = $this->option('force');

        $this->info("Cleaning up remote sessions inactive for more than {$inactiveMinutes} minutes...");

        // Find stale "active" sessions that should have ended
        $staleTime = Carbon::now()->subMinutes($inactiveMinutes);

        $staleSessions = DB::table('remote_sessions')
            ->where('status', 'ended')
            ->where('updated_at', '<', $staleTime)
            ->get();

        if ($staleSessions->isEmpty()) {
            $this->info('✅ No stale sessions found.');
            return Command::SUCCESS;
        }

        $this->warn("Found " . count($staleSessions) . " stale sessions:");
        
        foreach ($staleSessions as $session) {
            $this->line("  - Session #{$session->id}: Device {$session->remote_id}, User {$session->user_id}, last update: {$session->updated_at}");
        }

        if (!$force && !$this->confirm('Delete these sessions?', false)) {
            $this->info('Cleanup cancelled.');
            return Command::SUCCESS;
        }

        // Delete stale sessions
        $deleted = DB::table('remote_sessions')
            ->where('status', 'ended')
            ->where('updated_at', '<', $staleTime)
            ->delete();

        $this->info("✅ Deleted {$deleted} stale session records.");

        // Also check for orphaned sessions (device/user doesn't exist)
        $orphaned = DB::table('remote_sessions')
            ->leftJoin('remotes', 'remote_sessions.remote_id', '=', 'remotes.id')
            ->leftJoin('users', 'remote_sessions.user_id', '=', 'users.id')
            ->whereNull('remotes.id')
            ->orWhereNull('users.id')
            ->count();

        if ($orphaned > 0) {
            $this->warn("⚠️  Found {$orphaned} orphaned sessions (device/user deleted).");
            
            if ($force || $this->confirm('Delete orphaned sessions?', false)) {
                $deletedOrphaned = DB::table('remote_sessions')
                    ->leftJoin('remotes', 'remote_sessions.remote_id', '=', 'remotes.id')
                    ->leftJoin('users', 'remote_sessions.user_id', '=', 'users.id')
                    ->where(function ($query) {
                        $query->whereNull('remotes.id')
                              ->orWhereNull('users.id');
                    })
                    ->delete();
                
                $this->info("✅ Deleted {$deletedOrphaned} orphaned session records.");
            }
        } else {
            $this->info('✅ No orphaned sessions found.');
        }

        // Log statistics
        $stats = [
            'total' => DB::table('remote_sessions')->count(),
            'active' => DB::table('remote_sessions')->where('status', 'active')->count(),
            'ended' => DB::table('remote_sessions')->where('status', 'ended')->count(),
        ];

        $this->info('Session Statistics:');
        $this->line("  Total: {$stats['total']}");
        $this->line("  Active: {$stats['active']}");
        $this->line("  Ended: {$stats['ended']}");

        return Command::SUCCESS;
    }
}
