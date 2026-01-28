<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MediaPlaybackDurationChart extends ChartWidget
{
    protected static ?string $heading = 'Graphic Display Kiosk Of Media Playlist';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        try {
            // Fetch data from the external service
            $response = Http::timeout(10)
                ->get(env('SERVICE_REMOTE_DEVICE') . '/graph_playlist');

            // Check if the response is successful
            if ($response->successful()) {
                $result_name = $response->json('name', []);
                $result_total = $response->json('total', []);
            } else {
                Log::error('Failed to fetch data: ' . $response->status());
                $result_name = [];
                $result_total = [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching data: ' . $e->getMessage());
            $result_name = [];
            $result_total = [];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Graphic Layout Of Media Playlist',
                    'data' => $result_total,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $result_name,
        ];
    }

    public function getColumnSpan(): int
    {
        return 3;
    }
}
