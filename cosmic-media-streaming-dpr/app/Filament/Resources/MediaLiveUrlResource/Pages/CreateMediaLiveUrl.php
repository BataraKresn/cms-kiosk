<?php

namespace App\Filament\Resources\MediaLiveUrlResource\Pages;

use App\Filament\Resources\MediaLiveUrlResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaLiveUrl extends CreateRecord
{
    protected static string $resource = MediaLiveUrlResource::class;
    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/media-live-urls');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media-live-urls')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
