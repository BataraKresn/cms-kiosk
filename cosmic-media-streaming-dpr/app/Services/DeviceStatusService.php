<?php

namespace App\Services;

use App\Repositories\RemoteRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class DeviceStatusService
{
    protected RemoteRepository $repository;

    public function __construct(RemoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getChartData(): array
    {
        try {
            $connectedDevices = $this->repository->getConnectedDevice();
            $disconnectedDevices = $this->repository->getDisconnectDevice();

            $connectCount = $connectedDevices->count();
            $disconnectCount = $disconnectedDevices->count();

            $disconnectedName = $disconnectedDevices->pluck('name')->toArray();
            $connectedName = $connectedDevices->pluck('name')->toArray();

            return [
                'labels' => [
                    array_merge(['Connected'], $connectedName),
                    array_merge(['Disconnected'], $disconnectedName)
                ],
                'datasets' => [
                    [
                        'label' => 'Device Status',
                        'data' => [
                            $connectCount,
                            $disconnectCount
                        ],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)', // Blue for connected
                            'rgb(255, 99, 132)', // Red for disconnected
                        ],
                        'hoverOffset' => 4,
                    ],
                ],
            ];
        } catch (Exception $e) {
            Log::error('DeviceStatusChart Error: ' . $e->getMessage());
            return $this->getFallbackData();
        };
    }

    public function getFallbackData(): array
    {
        return [
            'labels' => ['Connected', 'Disconnected'],
            'datasets' => [
                [
                    'label' => 'Device Status',
                    'data' => [0, 0], // Default values when data cannot be fetched
                    'backgroundColor' => [
                        'rgb(54, 162, 235)', // Blue for connected
                        'rgb(255, 99, 132)', // Red for disconnected
                    ],
                    'hoverOffset' => 4,
                ],
            ],
        ];
    }
}
