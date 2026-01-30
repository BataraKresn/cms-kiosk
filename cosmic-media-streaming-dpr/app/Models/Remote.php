<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remote extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'device_identifier',
        'token',
        'mac_address',
        'ip_address',
        'android_version',
        'app_version',
        'battery_level',
        'wifi_strength',
        'last_seen_at',
        'status',
        'remote_control_enabled',
        'remote_control_port',
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
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_crash_at' => 'datetime',
        'screen_on' => 'boolean',
        'remote_control_enabled' => 'boolean',
    ];
}
