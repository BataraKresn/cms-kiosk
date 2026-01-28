<?php

namespace App\Filament\Resources\MediaSliderContentResource\Pages;

use App\Filament\Resources\MediaSliderContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaSliderContent extends CreateRecord
{
    protected static string $resource = MediaSliderContentResource::class;
    protected static bool $canCreateAnother = false;
}
