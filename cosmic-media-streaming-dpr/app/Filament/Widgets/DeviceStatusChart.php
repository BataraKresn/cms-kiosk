<?php

namespace App\Filament\Widgets;

use App\Models\Remote;
use App\Services\DeviceStatusService;
use Exception;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Device Status';
    protected static ?string $model = Remote::class;
    protected DeviceStatusService $service;
    protected bool $hasData = false;
    
    // Auto-refresh setiap 30 detik untuk detect device baru
    protected static ?string $pollingInterval = '30s';

    public function boot(): void
    {
        $this->service = app(DeviceStatusService::class);
    }

    protected function getData(): array
    {
        $data = $this->service->getChartData();
        $total = array_sum($data['datasets'][0]['data'] ?? [0]);
        $this->hasData = $total > 0;
        
        // Return empty to hide chart if no data
        if (!$this->hasData) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
        
        return $data;
    }

    public function getDescription(): ?string
    {
        if (!$this->hasData) {
            return '📱 No devices registered yet. Devices will appear automatically when kiosk APK is installed and connected.';
        }
        
        $data = $this->service->getChartData();
        $connected = $data['datasets'][0]['data'][0] ?? 0;
        $disconnected = $data['datasets'][0]['data'][1] ?? 0;
        
        return "🟢 Connected: {$connected} | 🔴 Disconnected: {$disconnected}";
    }

    protected function getType(): string
    {
        return 'pie';
    }

    public function getColumnSpan(): int
    {
        return 3; // Adjust the column span if needed
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 1.1,
            'plugins' => [
                'legend' => [
                    'display' => false,
                    'position' => 'bottom',
                    'labels' => [
                        'font' => ['size' => 12],
                        'color' => '#333',
                    ],
                ],
                'tooltip' => ['enabled' => true],
                'datalabels' => [
                    'display' => true,
                    'color' => '#FFF',
                    'backgroundColor' => '#000',
                    'borderRadius' => 3,
                    'formatter' => function ($value, $context) {
                        $total = array_sum($context['dataset']['data']);
                        $percentage = round(($value / $total) * 100);
                        return $percentage . '%';
                    },
                ],
            ],
        ];
    }
}
