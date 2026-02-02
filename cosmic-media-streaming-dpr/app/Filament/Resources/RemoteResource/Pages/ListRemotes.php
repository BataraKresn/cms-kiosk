<?php

namespace App\Filament\Resources\RemoteResource\Pages;

use App\Filament\Resources\RemoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRemotes extends ListRecords
{
    protected static string $resource = RemoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Devices')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at'))
                ->badge(fn () => \App\Models\Remote::whereNull('deleted_at')->count()),
            'deleted' => Tab::make('Deleted Devices')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(fn () => \App\Models\Remote::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
}
