<?php

namespace App\Filament\Resources\LayoutResource\Pages;

use App\Filament\Resources\LayoutResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListLayouts extends ListRecords
{
    protected static string $resource = LayoutResource::class;

    // Adding the "Create" button to the header actions
    protected function getHeaderActions(): array
    {
        return [
            // This will create the "Create" button in the header
            Actions\CreateAction::make(),
            ExportAction::make()
            ->exports([
                ExcelExport::make()
                    ->withColumns([
                        Column::make('name')
                            ->heading('Layout Name'),
                        Column::make('screen.name')
                            ->heading('Screen'),
                        Column::make('created_at')
                            ->heading('Created At')
                    ])
                    ->fromTable()
                    ->only([
                        'name',
                        'screen.name',
                        'created_at'
                    ])
                    ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV),
            ]),

        ];
    }

    // Defining the tabs for filtering the records
    public function getTabs(): array
    {
        return [
            // The default 'all' tab to show all records
            'all' => Tab::make(),

            // Tab to filter and show only templates
            'Template' => Tab::make()->modifyQueryUsing(
                fn(Builder $query) => $query->where('is_template', true)
            ),

            // Tab to filter and show only layouts
            'Layout' => Tab::make()->modifyQueryUsing(
                fn(Builder $query) => $query->where('is_template', false)
            ),
        ];
    }
}
