<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Heartbeat Management Fields to Remotes Table
 * 
 * This migration adds fields to support:
 * - Server-side heartbeat enforcement
 * - Grace period handling
 * - Status change tracking
 * - Reconnection signaling
 * - External service coordination
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            // Heartbeat timing configuration (per-device overrides)
            $table->unsignedInteger('heartbeat_interval_seconds')
                ->default(30)
                ->after('status')
                ->comment('Expected heartbeat interval in seconds');
            
            $table->unsignedInteger('grace_period_seconds')
                ->default(60)
                ->after('heartbeat_interval_seconds')
                ->comment('Grace period before marking offline (2x heartbeat)');
            
            // Reconnection signaling (server-initiated)
            $table->boolean('should_reconnect')
                ->default(false)
                ->after('grace_period_seconds')
                ->comment('Flag to signal device it should reconnect');
            
            $table->unsignedInteger('reconnect_delay_seconds')
                ->nullable()
                ->after('should_reconnect')
                ->comment('Seconds to wait before reconnecting (null = immediate)');
            
            $table->string('reconnect_reason', 255)
                ->nullable()
                ->after('reconnect_delay_seconds')
                ->comment('Reason for reconnection request');
            
            // Status transition tracking
            $table->timestamp('last_status_change_at')
                ->nullable()
                ->after('reconnect_reason')
                ->comment('When status last changed');
            
            $table->string('status_change_reason', 255)
                ->nullable()
                ->after('last_status_change_at')
                ->comment('Why status changed (for debugging)');
            
            $table->string('previous_status', 50)
                ->nullable()
                ->after('status_change_reason')
                ->comment('Previous status value before transition');
            
            // Rate limiting tracking
            $table->timestamp('last_heartbeat_received_at')
                ->nullable()
                ->after('previous_status')
                ->comment('Timestamp of last heartbeat received (for rate limiting)');
            
            $table->unsignedInteger('heartbeat_count_current_minute')
                ->default(0)
                ->after('last_heartbeat_received_at')
                ->comment('Counter for rate limiting (resets every minute)');
            
            // External service coordination
            $table->string('last_heartbeat_source', 20)
                ->default('device')
                ->after('heartbeat_count_current_minute')
                ->comment('Source of last status update: device|external_service|system');
            
            $table->timestamp('last_external_ping_at')
                ->nullable()
                ->after('last_heartbeat_source')
                ->comment('Last time external service pinged device');
            
            $table->string('external_ping_status', 50)
                ->nullable()
                ->after('last_external_ping_at')
                ->comment('Result of last external ping');
            
            // Indexes for performance
            $table->index('last_seen_at', 'idx_remotes_last_seen');
            $table->index('should_reconnect', 'idx_remotes_should_reconnect');
            $table->index(['status', 'last_status_change_at'], 'idx_remotes_status_tracking');
            $table->index('last_heartbeat_received_at', 'idx_remotes_heartbeat_rate');
        });

        // Update existing records with sensible defaults
        DB::table('remotes')->update([
            'last_status_change_at' => DB::raw('COALESCE(last_seen_at, updated_at)'),
            'status_change_reason' => 'Migration: Initial status',
            'last_heartbeat_source' => 'unknown',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->dropIndex('idx_remotes_heartbeat_rate');
            $table->dropIndex('idx_remotes_status_tracking');
            $table->dropIndex('idx_remotes_should_reconnect');
            $table->dropIndex('idx_remotes_last_seen');
            
            $table->dropColumn([
                'heartbeat_interval_seconds',
                'grace_period_seconds',
                'should_reconnect',
                'reconnect_delay_seconds',
                'reconnect_reason',
                'last_status_change_at',
                'status_change_reason',
                'previous_status',
                'last_heartbeat_received_at',
                'heartbeat_count_current_minute',
                'last_heartbeat_source',
                'last_external_ping_at',
                'external_ping_status',
            ]);
        });
    }
};
