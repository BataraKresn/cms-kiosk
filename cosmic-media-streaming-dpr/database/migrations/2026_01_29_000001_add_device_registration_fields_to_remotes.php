<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add device auto-registration fields to remotes table
     */
    public function up(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            // Device identification
            $table->string('device_identifier', 255)->nullable()->unique()->after('name')
                ->comment('Unique Android device ID for auto-registration');
            
            $table->string('token', 64)->nullable()->unique()->after('device_identifier')
                ->comment('Authentication token for WebSocket connection');
            
            // Device metadata
            $table->string('mac_address', 17)->nullable()->after('url');
            $table->string('ip_address', 45)->nullable()->after('mac_address')
                ->comment('Device IP address (Tailscale or local)');
            $table->string('app_version', 50)->nullable()->after('android_version');
            
            // Device status
            $table->integer('battery_level')->nullable()->after('app_version')
                ->comment('Battery percentage 0-100');
            $table->integer('wifi_strength')->nullable()->after('battery_level')
                ->comment('WiFi signal strength in dBm');
            $table->timestamp('last_seen_at')->nullable()->after('last_seen')
                ->comment('Last heartbeat timestamp');
            
            // Add indexes for performance
            $table->index('device_identifier');
            $table->index('token');
            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->dropIndex(['device_identifier']);
            $table->dropIndex(['token']);
            $table->dropIndex(['last_seen_at']);
            
            $table->dropColumn([
                'device_identifier',
                'token',
                'mac_address',
                'ip_address',
                'app_version',
                'battery_level',
                'wifi_strength',
                'last_seen_at',
            ]);
        });
    }
};
