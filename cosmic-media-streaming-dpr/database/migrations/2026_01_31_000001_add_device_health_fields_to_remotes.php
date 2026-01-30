<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add device health monitoring fields to remotes table
     */
    public function up(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            // Screen status
            $table->boolean('screen_on')->nullable()->after('wifi_strength')
                ->comment('Is device screen currently on?');
            
            // Storage
            $table->bigInteger('storage_available_mb')->nullable()->after('screen_on')
                ->comment('Available storage space in MB');
            $table->bigInteger('storage_total_mb')->nullable()->after('storage_available_mb')
                ->comment('Total storage space in MB');
            
            // Memory (RAM)
            $table->integer('ram_usage_mb')->nullable()->after('storage_total_mb')
                ->comment('Current RAM usage in MB');
            $table->integer('ram_total_mb')->nullable()->after('ram_usage_mb')
                ->comment('Total RAM in MB');
            
            // CPU & Temperature
            $table->decimal('cpu_temp', 6, 2)->nullable()->after('ram_total_mb')
                ->comment('CPU temperature in Celsius (max 9999.99)');
            
            // Network
            $table->string('network_type', 20)->nullable()->after('cpu_temp')
                ->comment('WiFi, Mobile Data, or None');
            
            // Current content
            $table->text('current_url')->nullable()->after('network_type')
                ->comment('Currently displayed URL in WebView');
            
            // Crash logs
            $table->text('last_crash_log')->nullable()->after('current_url')
                ->comment('Last app crash stack trace');
            $table->timestamp('last_crash_at')->nullable()->after('last_crash_log')
                ->comment('When the last crash occurred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->dropColumn([
                'screen_on',
                'storage_available_mb',
                'storage_total_mb',
                'ram_usage_mb',
                'ram_total_mb',
                'cpu_temp',
                'network_type',
                'current_url',
                'last_crash_log',
                'last_crash_at',
            ]);
        });
    }
};
