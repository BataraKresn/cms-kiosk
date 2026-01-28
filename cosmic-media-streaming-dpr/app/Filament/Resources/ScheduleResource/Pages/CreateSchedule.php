<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;


class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Create')
                ->action(function () {
                    $this->create();

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
