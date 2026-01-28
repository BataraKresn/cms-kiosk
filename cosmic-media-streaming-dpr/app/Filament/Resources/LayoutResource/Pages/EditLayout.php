<?php

namespace App\Filament\Resources\LayoutResource\Pages;

use App\Filament\Resources\LayoutResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditLayout extends EditRecord
{
    protected static string $resource = LayoutResource::class;

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
                    return redirect(env('URL_APP') . '/back-office/layouts');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/layouts')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
