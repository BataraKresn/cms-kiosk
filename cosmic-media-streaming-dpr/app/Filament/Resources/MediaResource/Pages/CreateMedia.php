<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions\Action;
// use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

// class CreateMedia extends Page
class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;
    protected static bool $canCreateAnother = false;

    // protected static string $view = 'filament.resources.media-resource.pages.create-media-custom';

    // protected function handleRecordCreation(array $data): Model
    // {
    //     $record = new ($this->getModel())([
    //         'name' => $data['name'],
    //         'description' => $data['description'],
    //         'type' => $data['type'],
    //     ]);

    //     switch ($variable) {
    //         case 'value':
    //             # code...
    //             break;

    //         default:
    //             throw new Exception('Invalid type');
    //             break;
    //     }
    //     return static::getModel()::create($data);
    // }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

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
