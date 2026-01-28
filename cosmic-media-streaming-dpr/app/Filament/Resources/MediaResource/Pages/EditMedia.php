<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('save_change')
                ->label('Save changes')
                ->action(function () {
                    $this->save();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/media');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
