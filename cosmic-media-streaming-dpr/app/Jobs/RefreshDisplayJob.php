<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshDisplayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;
    protected $urlAPI;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(string $token, string $urlAPI)
    {
        $this->token = $token;
        $this->urlAPI = $urlAPI;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $url = $this->urlAPI . '/send_refresh_device?token=' . $this->token;
            
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get($url);

            if ($response->successful()) {
                Log::info('Display refreshed successfully', [
                    'token' => $this->token,
                    'response' => $response->body()
                ]);
            } else {
                Log::error('Failed to refresh display', [
                    'token' => $this->token,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                // Retry the job if it fails
                throw new \Exception('Failed to refresh display');
            }
        } catch (\Exception $e) {
            Log::error('Error refreshing display', [
                'token' => $this->token,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('RefreshDisplayJob failed after all retries', [
            'token' => $this->token,
            'error' => $exception->getMessage()
        ]);
    }
}
