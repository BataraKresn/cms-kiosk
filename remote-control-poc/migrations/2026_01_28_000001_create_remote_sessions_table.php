<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remote Control Sessions Migration
 * 
 * This table tracks all remote control sessions (active and historical).
 * Used for:
 * - Session management
 * - Audit logging
 * - Analytics (who accessed which device, when, for how long)
 * - Billing (if implementing per-session charging)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remote_sessions', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('remote_id')
                ->constrained('remotes')
                ->onDelete('cascade')
                ->comment('Device being controlled');
                
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('User controlling the device');
            
            // Session info
            $table->string('session_token', 64)->unique()->comment('Unique session identifier');
            $table->enum('status', ['active', 'ended', 'error', 'timeout'])
                ->default('active')
                ->index()
                ->comment('Session status');
            
            // Timestamps
            $table->timestamp('started_at')->useCurrent()->comment('Session start time');
            $table->timestamp('ended_at')->nullable()->comment('Session end time');
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Total session duration');
            
            // Connection info
            $table->string('viewer_ip', 45)->nullable()->comment('IP address of viewer (CMS)');
            $table->string('viewer_user_agent', 255)->nullable()->comment('Browser user agent');
            $table->string('relay_server_id', 50)->nullable()->comment('Which relay server handled this session');
            
            // Statistics
            $table->unsignedBigInteger('frames_sent')->default(0)->comment('Total frames transmitted');
            $table->unsignedBigInteger('input_commands_sent')->default(0)->comment('Total input commands sent');
            $table->unsignedBigInteger('bytes_transferred')->default(0)->comment('Total bytes transferred');
            $table->float('average_fps', 5, 2)->nullable()->comment('Average frames per second');
            $table->unsignedInteger('average_latency_ms')->nullable()->comment('Average input latency');
            
            // Quality metrics
            $table->unsignedTinyInteger('quality_setting')->default(75)->comment('JPEG quality (0-100)');
            $table->unsignedTinyInteger('target_fps')->default(30)->comment('Target frame rate');
            
            // Error tracking
            $table->text('error_message')->nullable()->comment('Error message if session failed');
            $table->unsignedInteger('disconnection_count')->default(0)->comment('Number of reconnections');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['remote_id', 'status']);
            $table->index(['user_id', 'started_at']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_sessions');
    }
};
