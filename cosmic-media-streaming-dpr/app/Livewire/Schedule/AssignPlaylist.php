<?php

namespace App\Livewire\Schedule;

use App\Enums\DayEnum;
use App\Models\Playlist;
use App\Models\Schedule;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AssignPlaylist extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];

    public Schedule $record;

    public function mount(): void
    {
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Setup Schedule')
                ->description('You can setup schedule whole week')
                ->schema([
                    Card::make()->columns(2)
                        ->schema([
                            Forms\Components\Checkbox::make('is_whole_week')->label('Is Whole Week?')->live()->helperText("If you select 'all day,' then only a single playlist can be selected. Otherwise, you can set up a playlist at a specific time. Make sure the start and end times are in the correct order."),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),

                Repeater::make('schedule_playlists')
                    ->label('Select Playlist')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('playlist_id')->label('')->options(Playlist::all()->pluck('name', 'id'))->searchable()->required(),
                        Forms\Components\Select::make('start_day')->label('')->options(DayEnum::getAsOptions())->prefixIcon('heroicon-m-play-circle')->default(DayEnum::MONDAY)->required(),
                        Forms\Components\Select::make('end_day')->label('')->options(DayEnum::getAsOptions())->prefixIcon('heroicon-m-play-pause')->default(DayEnum::FRIDAY)->required(),
                    ])
                    ->columns(3)
                    ->addActionLabel('Add New Playlist')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->maxItems(fn (Get $get) => $get('is_whole_week') == 'true' ? 1 : 7),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
    }

    public function render(): View
    {
        return view('livewire.schedule.assign-playlist');
    }

    public function saveAction(): Action
    {
        return Action::make('Save')
            ->label('Save Setup Schedule')
            // ->requiresConfirmation()
            ->icon('heroicon-o-play-pause')
            ->action(function () {
                $this->edit();
                Notification::make()
                    ->success()
                    ->title('Information')
                    ->body('Setup schedule saved.')
                    ->send();
            });
    }
}
