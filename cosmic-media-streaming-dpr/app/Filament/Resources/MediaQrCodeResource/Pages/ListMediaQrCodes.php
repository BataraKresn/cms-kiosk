<?php

namespace App\Filament\Resources\MediaQrCodeResource\Pages;

use App\Filament\Resources\MediaQrCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediaQrCodes extends ListRecords
{
    protected static string $resource = MediaQrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
