<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remote Control Permissions Migration
 * 
 * This table controls who can access and control which devices.
 * Implements role-based access control (RBAC) at the device level.
 * 
 * Use cases:
 * - Allow certain users to only VIEW specific devices
 * - Restrict CONTROL to managers/admins
 * - Enable/disable RECORDING per user/device
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remote_permissions', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('User who gets the permission');
                
            $table->bigInteger('remote_id')->nullable()->index()->comment('Specific device (NULL = all devices)');
            $table->foreign('remote_id')->references('id')->on('remotes')->onDelete('cascade');
            
            // Permissions
            $table->boolean('can_view')
                ->default(true)
                ->comment('Can view device screen');
                
            $table->boolean('can_control')
                ->default(false)
                ->comment('Can send input commands');
                
            $table->boolean('can_record')
                ->default(false)
                ->comment('Can record sessions');
            
            $table->boolean('can_adjust_quality')
                ->default(false)
                ->comment('Can adjust stream quality');
            
            // Time-based restrictions
            $table->time('allowed_start_time')->nullable()->comment('Allowed time window start (24h format)');
            $table->time('allowed_end_time')->nullable()->comment('Allowed time window end');
            $table->json('allowed_days')->nullable()->comment('Allowed days of week [0-6, 0=Sunday]');
            
            // Expiration
            $table->timestamp('expires_at')->nullable()->comment('Permission expiration date');
            
            // Metadata
            $table->foreignId('granted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Admin who granted this permission');
                
            $table->text('reason')->nullable()->comment('Reason for granting permission');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure unique combination (user + device)
            $table->unique(['user_id', 'remote_id']);
            
            // Indexes
            $table->index(['user_id', 'can_view']);
            $table->index(['remote_id', 'can_control']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_permissions');
    }
};
