<?php

namespace App\Filament\Resources\PlaylistResource\Pages;

use App\Filament\Resources\PlaylistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListPlaylists extends ListRecords
{
    protected static string $resource = PlaylistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportAction::make()
            ->exports([
                ExcelExport::make()
                    ->withColumns([
                        Column::make('name')
                            ->heading('Name'),
                        Column::make('playlist_layouts_count')
                            ->heading('Total Layout'),
                        Column::make('created_at')
                            ->heading('Created At')
                    ])
                    ->fromTable()
                    ->only([
                        'name',
                        'playlist_layouts_count',
                        'created_at'
                    ])
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV),
            ]),
        ];
    }
}
