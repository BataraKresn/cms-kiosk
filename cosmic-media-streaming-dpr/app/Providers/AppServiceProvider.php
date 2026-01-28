<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Replace Livewire FileUploadController with custom one (no signature check)
        // Issue: Load balanced setup with 3 containers causes signature mismatch
        $this->app->bind(
            \Livewire\Features\SupportFileUploads\FileUploadController::class,
            \App\Http\Controllers\CustomLivewireFileUploadController::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Force HTTPS URLs in production (behind reverse proxy)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Override MinIO temporary URL to use public domain instead of internal endpoint
        Storage::disk('minio')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            $publicUrl = env('MINIO_URL', env('AWS_URL'));
            return $publicUrl . '/' . ltrim($path, '/');
        });

        Filament::serving(function () {
            Filament::registerNavigationItems([
                NavigationItem::make($this->diskUsages())
                    ->url('#')
                    ->icon('heroicon-o-cloud')
                    ->activeIcon('heroicon-s-cloud')
                    ->sort(3),
            ]);
        });
    }

    private function diskUsages()
    {
        $diskPath = '/'; // Change this to the desired disk path
        $totalSpace = disk_total_space($diskPath);
        $freeSpace = disk_free_space($diskPath);
        $usedSpace = $totalSpace - $freeSpace;
        $usedSpacePercentage = ($usedSpace / $totalSpace) * 100;

        $label = sprintf('%s / %s (%s)%%', $this->formatSize($totalSpace), $this->formatSize($usedSpace, true), round($usedSpacePercentage, 2));

        return $label;
    }

    private function formatSize($size, $with_unit = false)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        $size = round($size, 2);

        if ($with_unit) {
            $size .= $units[$i];
        }

        return $size;
    }
}
