<?php

namespace App\Filament\Resources\MediaLiveUrlResource\Pages;

use App\Filament\Resources\MediaLiveUrlResource;
use App\Models\Display;
use App\Models\Media;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EditMediaLiveUrl extends EditRecord
{
    protected static string $resource = MediaLiveUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save_change')
                ->label('Save changes')
                ->action(function () {
                    $record = $this->getRecord();
                    $this->save();

                    if ($record && $record->id) {
                        Log::info('Refreshing displays for Live URL ID:' . $record->id);

                        try {
                            $media = Media::where('mediable_type', 'App\Models\MediaLiveUrl')
                                ->where('mediable_id', $record->id)
                                ->first();

                            if (!$media) {
                                Log::error('Media not found for Live URL ID: ' . $record->id);
                                return redirect(env('URL_APP') . '/back-office/media-live-urls');
                            }

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
                        } catch (\Exception $e) {
                            Log::error('Exception when refreshing displays: ' . $e->getMessage());
                        }
                    }

                    return redirect(env('URL_APP') . '/back-office/media-live-urls');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media-live-urls')
                ->color('danger'),
        ];
    }
}
