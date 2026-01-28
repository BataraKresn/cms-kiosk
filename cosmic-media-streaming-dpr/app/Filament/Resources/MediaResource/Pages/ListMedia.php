<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Enums\MediaTypeEnum;
use App\Filament\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use stdClass;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

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
                            Column::make('mediable.name')
                                ->heading('Content'),
                            Column::make('mediable_type')
                                ->heading('Type')
                                ->formatStateUsing(fn(string $state): string => MediaTypeEnum::getAsOptions()[$state]),
                            Column::make('created_at')
                                ->heading('Created At')
                        ])
                        ->fromTable()
                        ->only([
                            'name',
                            'mediable.name',
                            'mediable_type',
                            'created_at'
                        ])
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                ]), 
        ];
    }
}
