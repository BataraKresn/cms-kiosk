<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeviceApiService
{
    public function getDevices()
    {
        try {
            $remoteServiceUrl = env('SERVICE_REMOTE_DEVICE', 'http://127.0.0.1:3001');
            $response = Http::timeout(5)
                ->retry(3, 100)
                ->get("{$remoteServiceUrl}/list_devices");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch devices from remote service', [
                'url' => $remoteServiceUrl,
                'status' => $response->status(),
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching devices', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
