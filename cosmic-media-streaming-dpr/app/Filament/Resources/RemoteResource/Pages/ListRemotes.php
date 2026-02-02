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
                ->icon('heroicon-o-check-circle')
                ->badge(fn () => \App\Models\Remote::whereNull('deleted_at')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
            
            'deleted' => Tab::make('Deleted Devices')
                ->icon('heroicon-o-trash')
                ->badge(fn () => \App\Models\Remote::whereNotNull('deleted_at')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('deleted_at')),
        ];
    }
}
