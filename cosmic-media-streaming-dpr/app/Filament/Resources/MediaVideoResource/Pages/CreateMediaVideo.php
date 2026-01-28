<?php

namespace App\Filament\Resources\MediaVideoResource\Pages;

use App\Filament\Resources\MediaVideoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CreateMediaVideo extends CreateRecord
{
    protected static string $resource = MediaVideoResource::class;
    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        // Perform a redirect without breaking the method signature
        exit(header('Location: ' . env('URL_APP') . "/back-office/media-videos/createMediaVideo"));
    }

    // protected function getFormActions(): array
    // {
    //     return [
    //         Action::make('create')
    //             ->label('Create')
    //             ->action(function () {
    //                 $this->create();
    //                 return Redirect::to(env('URL_APP') . '/back-office/media-videos');
    //             }),

    //         Action::make('cancel')
    //             ->label('Cancel')
    //             ->url(env('URL_APP') . '/back-office/media-videos')  
    //             ->color('danger'),
    //     ];
    // }
}
