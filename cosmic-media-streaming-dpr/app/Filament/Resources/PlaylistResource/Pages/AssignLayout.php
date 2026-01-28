<?php

namespace App\Filament\Resources\PlaylistResource\Pages;

use App\Filament\Resources\PlaylistResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class AssignLayout extends EditRecord
{
    protected static string $resource = PlaylistResource::class;

    protected static string $view = 'filament.resources.playlist-resource.pages.assign-layout';

    protected function getFormActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/playlists')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
