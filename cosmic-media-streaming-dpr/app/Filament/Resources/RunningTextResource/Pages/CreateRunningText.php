<?php

namespace App\Filament\Resources\RunningTextResource\Pages;

use App\Filament\Resources\RunningTextResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateRunningText extends CreateRecord
{
    protected static string $resource = RunningTextResource::class;

    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/running-texts');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/running-texts')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
