<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save_change')
                ->label('Save changes')
                ->action(function () {
                    $this->save();

                    // Redirect to the index page after saving the record
                    return redirect(env('URL_APP') . '/back-office/schedules');
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->url(env('URL_APP') . '/back-office/schedules')  // Adjust the cancel URL if needed
                ->color('danger'),
        ];
    }
}
