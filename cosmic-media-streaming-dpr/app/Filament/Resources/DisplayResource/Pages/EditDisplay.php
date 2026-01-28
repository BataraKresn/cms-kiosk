<?php

namespace App\Filament\Resources\DisplayResource\Pages;

use App\Filament\Resources\DisplayResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EditDisplay extends EditRecord
{
    protected static string $resource = DisplayResource::class;

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
                    $record = $this->getRecord(); // Ensure the record is retrieved correctly

                    $this->form->getState(); // Ensure form state is updated before saving
                    $this->save(); // Save the record

                    $urlAPI = env('URL_PDF');
                    Log::info('API URL: ' . $urlAPI);

                    if ($record && $record->token) {
                        $url = $urlAPI . '/send_refresh_device?token=' . $record->token;
                        Log::info('Request URL: ' . $url);

                        $response = Http::get($url);

                        // Log the response for debugging
                        if ($response->successful()) {
                            Log::info('Refresh Device response:', ['response' => $response->body()]);
                        } else {
                            Log::error('Failed to refresh device:', ['response' => $response->body()]);
                        }
                    } else {
                        Log::error('Record or token is missing.');
                    }

                    // Redirect after saving
                    return redirect()->to(env('URL_APP') . '/back-office/displays');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/displays')  
                ->color('danger'),
        ];
    }
}
