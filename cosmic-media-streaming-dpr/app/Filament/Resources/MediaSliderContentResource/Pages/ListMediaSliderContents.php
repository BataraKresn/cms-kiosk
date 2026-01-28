<?php

namespace App\Filament\Resources\MediaSliderContentResource\Pages;

use App\Filament\Resources\MediaSliderContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediaSliderContents extends ListRecords
{
    protected static string $resource = MediaSliderContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
