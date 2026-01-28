<?php

namespace App\Filament\Resources\DisplayResource\Pages;

use App\Filament\Resources\DisplayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDisplay extends CreateRecord
{
    protected static string $resource = DisplayResource::class;

    protected static bool $canCreateAnother = false;
}
