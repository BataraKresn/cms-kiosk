<?php

namespace App\Filament\Resources\MediaHtmlResource\Pages;

use App\Filament\Resources\MediaHtmlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListMediaHtmls extends ListRecords
{
    protected static string $resource = MediaHtmlResource::class;

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
                        Column::make('created_at')
                            ->heading('Created At')
                    ])
                    ->fromTable()
                    ->only([
                        'name',
                        'created_at'
                    ])
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ]),
        ];
    }
}
