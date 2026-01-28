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

    public function boot(): void
    {
        $this->service = app(DeviceStatusService::class);
    }

    protected function getData(): array
    {
        return $this->service->getChartData();
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
