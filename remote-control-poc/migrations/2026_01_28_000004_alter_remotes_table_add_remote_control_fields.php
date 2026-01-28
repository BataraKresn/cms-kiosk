<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alter Remotes Table - Add Remote Control Fields
 * 
 * This migration adds new fields to the existing 'remotes' table
 * to support remote control functionality.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            // Remote control feature toggle
            $table->boolean('remote_control_enabled')
                ->default(false)
                ->after('status')
                ->comment('Is remote control enabled for this device');
            
            // Connection info
            $table->unsignedSmallInteger('remote_control_port')
                ->default(5555)
                ->after('remote_control_enabled')
                ->comment('WebSocket port for remote control');
            
            // Device capabilities
            $table->string('screen_resolution', 20)
                ->nullable()
                ->after('remote_control_port')
                ->comment('Screen resolution (e.g., 1080x1920)');
                
            $table->unsignedTinyInteger('screen_density')
                ->nullable()
                ->after('screen_resolution')
                ->comment('Screen density (DPI)');
            
            $table->string('android_version', 20)
                ->nullable()
                ->after('screen_density')
                ->comment('Android OS version');
            
            // Service status
            $table->boolean('capture_service_running')
                ->default(false)
                ->after('android_version')
                ->comment('Is ScreenCaptureService running');
            
            $table->boolean('input_service_enabled')
                ->default(false)
                ->after('capture_service_running')
                ->comment('Is AccessibilityService enabled');
            
            // Last activity
            $table->timestamp('last_frame_at')
                ->nullable()
                ->after('input_service_enabled')
                ->comment('Last time a frame was received');
            
            $table->timestamp('last_input_at')
                ->nullable()
                ->after('last_frame_at')
                ->comment('Last time input command was sent');
            
            // Performance metrics
            $table->float('current_fps', 5, 2)
                ->nullable()
                ->after('last_input_at')
                ->comment('Current streaming FPS');
            
            $table->unsignedInteger('current_latency_ms')
                ->nullable()
                ->after('current_fps')
                ->comment('Current input latency in milliseconds');
            
            $table->float('bandwidth_mbps', 8, 2)
                ->nullable()
                ->after('current_latency_ms')
                ->comment('Current bandwidth usage');
            
            // Active session
            $table->foreignId('active_session_id')
                ->nullable()
                ->after('bandwidth_mbps')
                ->constrained('remote_sessions')
                ->nullOnDelete()
                ->comment('Currently active remote control session');
            
            // Indexes for performance
            $table->index('remote_control_enabled');
            $table->index(['remote_control_enabled', 'status']);
            $table->index('last_frame_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->dropForeign(['active_session_id']);
            $table->dropIndex(['remote_control_enabled']);
            $table->dropIndex(['remote_control_enabled', 'status']);
            $table->dropIndex(['last_frame_at']);
            
            $table->dropColumn([
                'remote_control_enabled',
                'remote_control_port',
                'screen_resolution',
                'screen_density',
                'android_version',
                'capture_service_running',
                'input_service_enabled',
                'last_frame_at',
                'last_input_at',
                'current_fps',
                'current_latency_ms',
                'bandwidth_mbps',
                'active_session_id',
            ]);
        });
    }
};
