<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Device::query()->truncate();


        Device::create([
            'url_device' => '100.123.141.43:1111',
            'device_model' => 'SM-M236B',
            'android_version' => '14',
            'status_device' => 'Device Connected',
        ]);
    }
}
