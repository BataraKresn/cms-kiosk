<?php

namespace App\Filament\Resources\RemoteResource\Pages;

use App\Filament\Resources\RemoteResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use App\Models\Remote; // Add the Remote model if needed

class CreateRemote extends CreateRecord
{
    protected static string $resource = RemoteResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/remotes');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/remotes')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
