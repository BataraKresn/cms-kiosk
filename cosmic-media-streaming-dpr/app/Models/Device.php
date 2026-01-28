<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Sushi\Sushi;

class Device extends Model
{
    use Sushi;

    public function getRows(): array
    {
        try {
            // Fetch devices with a timeout and automatic retries
            $response = Http::timeout(5)
                ->retry(3, 100) // Retry up to 3 times with a 100ms delay
                ->get(env('SERVICE_REMOTE_DEVICE') . '/list_devices');

            if ($response->successful() && isset($response['data'])) {
                // Filter and map required fields in one pass
                return collect($response['data'])->map(fn($item) => [
                    "id" => $item['id'] ?? null,
                    "name" => $item['name'] ?? null,
                    "serial_device" => $item['serial_device'] ?? null,
                    "ip_device" => $item['ip_device'] ?? null,
                    "port_device" => $item['port_device'] ?? null,
                    "status_device" => $item['status_device'] ?? null,
                    "created_at" => $item['created_at'] ?? null,
                ])->toArray();
            }

            // Log only if the response fails
            \Log::error('Failed to fetch devices', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            // Log exceptions with minimal overhead
            \Log::error('Error fetching devices', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
