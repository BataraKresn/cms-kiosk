<?php

namespace App\Filament\Resources\MediaImageResource\Pages;

use App\Filament\Resources\MediaImageResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMediaImage extends EditRecord
{
    protected static string $resource = MediaImageResource::class;

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
                    $this->save();

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
