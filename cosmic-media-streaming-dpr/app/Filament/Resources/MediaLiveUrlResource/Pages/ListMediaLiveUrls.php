<?php

namespace App\Filament\Resources\MediaLiveUrlResource\Pages;

use App\Filament\Resources\MediaLiveUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListMediaLiveUrls extends ListRecords
{
    protected static string $resource = MediaLiveUrlResource::class;

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
                        Column::make('url')
                            ->heading('Live URL'),
                        Column::make('created_at')
                            ->heading('Created At')
                    ])
                    ->fromTable()
                    ->only([
                        'name',
                        'url',
                        'created_at'
                    ])
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ]),

        ];
    }
}
