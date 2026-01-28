<?php

namespace App\Filament\Resources\MediaQrCodeResource\Pages;

use App\Filament\Resources\MediaQrCodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaQrCode extends CreateRecord
{
    protected static string $resource = MediaQrCodeResource::class;
    protected static bool $canCreateAnother = false;
}
