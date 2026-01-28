<?php

namespace App\Filament\Resources\MediaHlsResource\Pages;

use App\Filament\Resources\MediaHlsResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaHls extends CreateRecord
{
    protected static string $resource = MediaHlsResource::class;
    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/media-hls');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media-hls')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
