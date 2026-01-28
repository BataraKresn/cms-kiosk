<?php

namespace App\Filament\Resources\MediaSliderResource\Pages;

use App\Filament\Resources\MediaSliderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaSlider extends CreateRecord
{
    protected static string $resource = MediaSliderResource::class;
    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/media-sliders');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media-sliders')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
