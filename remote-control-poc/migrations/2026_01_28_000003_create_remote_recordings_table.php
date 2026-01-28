<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remote Recordings Migration
 * 
 * This table stores metadata about recorded remote control sessions.
 * The actual video files are stored in MinIO or local storage.
 * 
 * Use cases:
 * - Audit trail (security compliance)
 * - Training/documentation
 * - Troubleshooting (replay session to diagnose issues)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remote_recordings', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('session_id')
                ->constrained('remote_sessions')
                ->onDelete('cascade')
                ->comment('Session that was recorded');
            
            // File info
            $table->string('file_path', 500)->comment('Path to video file (MinIO or local)');
            $table->string('storage_disk', 50)->default('minio')->comment('Storage disk (minio, local, s3)');
            $table->string('file_name', 255)->comment('Original file name');
            $table->string('file_format', 10)->default('webm')->comment('Video format (webm, mp4, avi)');
            
            // Recording metadata
            $table->unsignedBigInteger('file_size_bytes')->default(0)->comment('File size in bytes');
            $table->float('file_size_mb', 10, 2)->virtualAs('file_size_bytes / 1048576')->comment('File size in MB');
            $table->unsignedInteger('duration_seconds')->comment('Recording duration');
            $table->string('duration_formatted', 20)->nullable()->comment('HH:MM:SS format');
            
            // Video properties
            $table->unsignedSmallInteger('width')->nullable()->comment('Video width in pixels');
            $table->unsignedSmallInteger('height')->nullable()->comment('Video height in pixels');
            $table->unsignedTinyInteger('fps')->nullable()->comment('Frames per second');
            $table->unsignedInteger('bitrate_kbps')->nullable()->comment('Video bitrate');
            $table->string('codec', 50)->nullable()->comment('Video codec used');
            
            // Processing status
            $table->enum('status', ['recording', 'processing', 'completed', 'failed', 'deleted'])
                ->default('recording')
                ->index()
                ->comment('Recording status');
                
            $table->text('processing_error')->nullable()->comment('Error during post-processing');
            
            // Access control
            $table->boolean('is_public')->default(false)->comment('Can be viewed by non-owners');
            $table->string('share_token', 64)->nullable()->unique()->comment('Token for sharing');
            $table->timestamp('share_expires_at')->nullable()->comment('Share link expiration');
            
            // Retention policy
            $table->timestamp('auto_delete_at')->nullable()->comment('Automatic deletion date');
            $table->boolean('is_archived')->default(false)->comment('Archived to cold storage');
            
            // Playback tracking
            $table->unsignedInteger('view_count')->default(0)->comment('Number of times played');
            $table->timestamp('last_viewed_at')->nullable()->comment('Last playback time');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['session_id', 'status']);
            $table->index('auto_delete_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_recordings');
    }
};
