<?php

namespace App\Filament\Resources\MediaQrCodeResource\Pages;

use App\Filament\Resources\MediaQrCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMediaQrCode extends EditRecord
{
    protected static string $resource = MediaQrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
