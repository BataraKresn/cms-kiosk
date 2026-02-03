<?php

namespace App\Filament\Resources\RemoteResource\Pages;

use App\Models\Remote;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RemoteControlViewer extends Page
{
    protected static string $resource = \App\Filament\Resources\RemoteResource::class;

    protected static string $view = 'filament.pages.remote-control-viewer';

    public Remote $record;
    
    // Properties for view
    public bool $canControl = true;
    public bool $canRecord = false;
    /**
     * Check if user can access this page
     */
    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            \Log::debug('RemoteControlViewer::canAccess - No authenticated user');
            return false;
        }
        
        // Check if user can view any remote
        $canViewAny = static::getResource()::canViewAny();
        
        \Log::debug('RemoteControlViewer::canAccess', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'canViewAny' => $canViewAny,
            'parameters' => $parameters,
        ]);
        
        return $canViewAny;
    }

    public function mount($record): void
    {
        \Log::info('RemoteControlViewer::mount called with record=' . $record);
        
        try {
            $this->record = Remote::findOrFail($record);
            \Log::info('Remote found: ' . $this->record->id);
        } catch (\Throwable $e) {
            \Log::error('Failed to find remote: ' . $e->getMessage(), ['record' => $record]);
            throw $e;
        }
        
        // Check if remote control is enabled
        if (!$this->record->remote_control_enabled) {
            \Log::info('Remote control disabled for record ' . $record);
            $this->redirect(route('filament.back-office.resources.remotes.index'));
        }
        
        // Set permissions based on user role (can be customized)
        $this->canControl = true; // Allow control by default
        $this->canRecord = auth()->user()->hasRole('admin'); // Only admin can record
        
        \Log::info('RemoteControlViewer::mount completed successfully');
    }

    public function getTitle(): string | Htmlable
    {
        return "Remote Control: {$this->record->name}";
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getDeviceId(): string
    {
        return (string) $this->record->id;
    }

    public function getRelayServerUrl(): string
    {
        // Get from environment or use default
        $wsProtocol = config('app.env') === 'local' ? 'ws' : 'wss';
        $host = request()->getHost();
        
        // Use nginx proxy path instead of direct port
        return "{$wsProtocol}://{$host}/remote-control-ws";
    }

    public function getSessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
