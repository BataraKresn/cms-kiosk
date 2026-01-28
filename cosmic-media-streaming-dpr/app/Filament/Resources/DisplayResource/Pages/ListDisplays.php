<?php

namespace App\Filament\Resources\DisplayResource\Pages;

use App\Filament\Resources\DisplayResource;
use App\Models\Display;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListDisplays extends ListRecords
{
    protected static string $resource = DisplayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('Create New Display Token')
                ->requiresConfirmation()
                ->icon('heroicon-o-device-phone-mobile')
                ->action(function () {
                    $display = new Display;
                    $display->newToken();

                    Notification::make()
                        ->title('New display token created.')
                        ->success()
                        ->send();

                    return redirect()->route('filament.back-office.resources.displays.edit', ['record' => $display]);
                }),
                ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->withColumns([
                            Column::make('name')
                                ->heading('Name'),
                            Column::make('schedule.name')
                                ->heading('Schedule'),
                            Column::make('screen.name')
                                ->heading('Screen'),
                            Column::make('token')
                                ->heading('Token'),
                            Column::make('created_at')
                                ->heading('Created At')
                        ])
                        ->fromTable()
                        ->only([
                            'name',
                            'schedule.name',
                            'screen.name',
                            'token',
                            'created_at'
                        ])
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV),
                ]),
        ];
    }
}
