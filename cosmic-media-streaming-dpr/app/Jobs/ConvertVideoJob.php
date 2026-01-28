<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Log;

class ConvertVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes for video processing
    public $tries = 3;
    public $backoff = 60; // Wait 60 seconds before retry

    protected $originalPath;
    protected $convertedPath;

    public function __construct($originalPath, $convertedPath)
    {
        $this->originalPath = $originalPath;
        $this->convertedPath = $convertedPath;
        
        // Route to VIDEO queue (processed by cosmic-queue-video-* workers)
        $this->onQueue('video');
    }

    public function handle()
    {
        // Convert relative path to absolute path
        $originalFullPath = storage_path("app/{$this->originalPath}");
        $convertedFullPath = storage_path("app/{$this->convertedPath}");

        if (!file_exists($originalFullPath)) {
            Log::error("FFmpeg Error: File not found at {$originalFullPath}");
            return;
        }

        FFMpeg::fromDisk('local')
            ->open($this->originalPath) // Use relative path inside storage
            ->export()
            ->toDisk('local')
            ->inFormat((new X264)->setKiloBitrate(1200))
            ->withVisibility('public')
            ->save($this->originalPath);

        // Delete original file after conversion
        Storage::disk('local')->delete($this->originalPath);
    }
}
