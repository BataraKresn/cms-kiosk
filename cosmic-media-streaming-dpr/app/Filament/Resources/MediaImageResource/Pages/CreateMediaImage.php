<?php

namespace App\Filament\Resources\MediaImageResource\Pages;

use App\Filament\Resources\MediaImageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaImage extends CreateRecord
{
    protected static string $resource = MediaImageResource::class;
    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/media-images');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media-images')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
