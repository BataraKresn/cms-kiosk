<?php

namespace App\Filament\Resources\MediaSliderContentResource\Pages;

use App\Filament\Resources\MediaSliderContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMediaSliderContent extends EditRecord
{
    protected static string $resource = MediaSliderContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
