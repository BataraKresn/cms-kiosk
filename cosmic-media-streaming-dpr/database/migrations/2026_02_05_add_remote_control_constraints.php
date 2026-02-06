<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add performance indexes for remote control feature
        // Foreign key constraints already exist from previous migrations
        
        // Index for session cleanup queries (status + started_at)
        try {
            DB::statement('CREATE INDEX idx_remote_sessions_status_started ON remote_sessions(status, started_at)');
        } catch (\Exception $e) {
            // Index may already exist
        }

        // Index for session history lookup (user_id + started_at)
        try {
            DB::statement('CREATE INDEX idx_remote_sessions_user_date ON remote_sessions(user_id, started_at DESC)');
        } catch (\Exception $e) {
            // Index may already exist
        }

        // Index for permission validation (user_id + remote_id)
        try {
            DB::statement('CREATE INDEX idx_remote_permissions_user_remote ON remote_permissions(user_id, remote_id)');
        } catch (\Exception $e) {
            // Index may already exist
        }

        // Index for device status queries (status + updated_at)
        try {
            DB::statement('CREATE INDEX idx_remotes_status ON remotes(status, updated_at)');
        } catch (\Exception $e) {
            // Index may already exist
        }

        // Unique constraint on token for fast lookups
        try {
            DB::statement('CREATE UNIQUE INDEX idx_remotes_token ON remotes(token)');
        } catch (\Exception $e) {
            // Index may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        $indexes = [
            'idx_remote_sessions_status_started',
            'idx_remote_sessions_user_date',
            'idx_remote_permissions_user_remote',
            'idx_remotes_status',
            'idx_remotes_token',
        ];

        foreach ($indexes as $index) {
            try {
                DB::statement("DROP INDEX {$index} ON remote_sessions");
            } catch (\Exception $e) {
                // Index doesn't exist on this table
            }

            try {
                DB::statement("DROP INDEX {$index} ON remote_permissions");
            } catch (\Exception $e) {
                // Index doesn't exist on this table
            }

            try {
                DB::statement("DROP INDEX {$index} ON remotes");
            } catch (\Exception $e) {
                // Index doesn't exist on this table
            }
        }
    }
};
