<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

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
                        Column::make('schedule_playlists_count')
                            ->heading('Total Playlist'),
                        Column::make('created_at')
                            ->heading('Created At')
                    ])
                    ->fromTable()
                    ->only([
                        'name',
                        'schedule_playlists_count',
                        'created_at'
                    ])
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV),
            ]),
        ];
    }
}
