<?php

namespace App\Livewire\Playlist;

use App\Models\Layout;
use App\Models\Playlist;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AssignLayout extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];
    public Playlist $record;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(): void
    {
        $this->record->load('playlist_layouts');
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        $layoutOptions = Cache::remember('layout_options', 60, function () {
            return Layout::valid()->select(['id', 'name'])->get()->pluck('name', 'id')->toArray();
        });

        // TODO: set all day?
        // if all day remove all layout
        // else configure dynamic layout select
        return $form
            ->schema([

                Section::make('Setup Playlist')
                    ->description('You can setup playlist all day')
                    ->schema([
                        Card::make()->columns(1)
                            ->schema([
                                Forms\Components\Checkbox::make('is_all_day')->label('Is All Day?')->helperText("If you select 'all day,' then only a single layout can be selected. Otherwise, you can set up a layout at a specific time. Make sure the start and end times are in the correct order."),
                                // Forms\Components\TextInput::make('layout_interval')->default(0)->numeric()->hint('In Minutes'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Repeater::make('playlist_layouts')
                    ->label('Select Layout')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('layout_id')->label('')->options(Layout::valid()->pluck('name', 'id'))->searchable()->required(),
                        Forms\Components\TimePicker::make('start_time')->label('')->prefixIcon('heroicon-m-play')->seconds(false)->required(),
                        Forms\Components\TimePicker::make('end_time')->label('')->prefixIcon('heroicon-m-stop')->seconds(false)->required(),
                    ])
                    ->defaultItems(1)
                    ->columns(3)
                    ->addActionLabel('Add New Layout')
                    ->reorderable(false)
                    ->minItems(1)
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                        $data['start_time'] = $data['start_time'] . ':01';
                        $data['end_time'] = $data['end_time'] . ':59';

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['start_time'] = $data['start_time'] . ':01';
                        $data['end_time'] = $data['end_time'] . ':59';

                        return $data;
                    }),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): void
    {
        $data = $this->form->getState();
        $this->record->update($data);

        Cache::forget('layout_options');
    }

    public function render(): View
    {
        return view('livewire.playlist.assign-layout');
    }

    public function saveAction(): Action
    {
        return Action::make('Save')
            ->label('Save Setup Playlist')
            // ->requiresConfirmation()
            ->icon('heroicon-o-play-pause')
            ->action(function () {
                $this->edit();
                Notification::make()
                    ->success()
                    ->title('Information')
                    ->body('Setup playlist saved.')
                    ->send();
            });
    }
}
