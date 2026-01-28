<?php

namespace App\Filament\Resources\MediaVideoResource\Pages;

use App\Filament\Resources\MediaVideoResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMediaVideo extends EditRecord
{
    protected static string $resource = MediaVideoResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    // protected function getFormActions(): array
    // {
    //     return [
    //         Action::make('save_change')
    //             ->label('Save changes')
    //             ->action(function () {
    //                 $this->save();

    //                 // Redirect to the index page after saving the record
    //                 return redirect(env('URL_APP') . '/back-office/media-videos');
    //             }),

    //         Action::make('cancel')
    //             ->label('Cancel')
    //             ->url(env('URL_APP') . '/back-office/media-videos')  // Adjust the cancel URL if needed
    //             ->color('danger'),
    //     ];
    // }
}
