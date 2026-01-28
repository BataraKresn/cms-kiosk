<?php

namespace App\Filament\Resources\MediaHtmlResource\Pages;

use App\Filament\Resources\MediaHtmlResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaHtml extends CreateRecord
{
    protected static string $resource = MediaHtmlResource::class;
    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/media-htmls');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/media-htmls')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
