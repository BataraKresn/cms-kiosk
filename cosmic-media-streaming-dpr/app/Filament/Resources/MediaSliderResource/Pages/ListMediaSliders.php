<?php

namespace App\Filament\Resources\MediaSliderResource\Pages;

use App\Filament\Resources\MediaSliderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;


class ListMediaSliders extends ListRecords
{
    protected static string $resource = MediaSliderResource::class;

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
                        Column::make('animation_type')
                            ->heading('Animation'),
                        Column::make('media_slider_contents_count')
                            ->heading('Total Content'),
                        Column::make('created_at')
                            ->heading('Created At')
                    ])
                    ->fromTable()
                    ->only([
                        'name',
                        'animation_type',
                        'media_slider_contents_count',
                        'created_at'
                    ])
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ]),

        ];
    }
}
