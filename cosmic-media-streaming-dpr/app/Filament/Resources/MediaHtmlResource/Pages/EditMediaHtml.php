<?php

namespace App\Filament\Resources\MediaHtmlResource\Pages;

use App\Filament\Resources\MediaHtmlResource;
use App\Models\Display;
use App\Models\Media;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class EditMediaHtml extends EditRecord
{
    protected static string $resource = MediaHtmlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->redirect(MediaHtmlResource::getUrl());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if ($record && $record->id) {
            Log::info('Refreshing displays for HTML ID:' . $record->id);

            try {
                $media = Media::where('mediable_type', 'App\Models\MediaHtml')
                    ->where('mediable_id', $record->id)
                    ->first();

                if ($media) {
                    $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                        $query->where('media_id', $media->id);
                    })->whereNotNull('token')->get();

                    $urlAPI = env('URL_PDF');
                    $successCount = 0;
                    $failCount = 0;

                    foreach ($displays as $display) {
                        if ($display->token) {
                            $url = $urlAPI . '/send_refresh_device?token=' . $display->token;
                            Log::info('Refreshing display with token: ' . $display->token);

                            $response = Http::get($url);

                            if ($response->successful()) {
                                Log::info('Refresh Device response:', ['response' => $response->body()]);
                                $successCount++;
                            } else {
                                Log::error('Failed to refresh device:', ['response' => $response->body()]);
                                $failCount++;
                            }
                        }
                    }

                    Log::info("Refreshed $successCount displays successfully. Failed: $failCount");
                } else {
                    Log::error('Media not found for HTML ID: ' . $record->id);
                }
            } catch (\Exception $e) {
                Log::error('Exception when refreshing displays: ' . $e->getMessage());
            }
        }

        return $data;
    }
}
