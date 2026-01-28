<?php

namespace App\Filament\Resources\LayoutResource\Pages;

use App\Filament\Resources\LayoutResource;
use Filament\Resources\Pages\EditRecord;

class AssignSpot extends EditRecord
{
    protected static string $resource = LayoutResource::class;

    protected static string $view = 'filament.resources.layout-resource.pages.assign-spot';
}
