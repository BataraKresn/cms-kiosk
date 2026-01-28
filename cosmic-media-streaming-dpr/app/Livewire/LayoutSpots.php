<?php

namespace App\Livewire;

use App\Enums\MediaTypeEnum;
use App\Models\Layout;
use App\Models\RunningText;
use App\Models\Spot;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class LayoutSpots extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public Layout $record;

    #[Locked]
    public $rowIncrement = 0;

    public function table(Table $table): Table
    {
        return $table
            ->query(Spot::where('layout_id', $this->record->id))
            ->columns([
                TextColumn::make('id')->label('')->rowIndex(isFromZero: false),
                TextColumn::make('media.name')->label('Media'),
                TextColumn::make('media.mediable_type')->label('Type')->formatStateUsing(fn(string $state): string => MediaTypeEnum::getAsOptions()[$state]),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        Select::make('media_id')
                            ->label('Media')
                            ->relationship(name: 'media')
                            ->getOptionLabelFromRecordUsing(function (Model $record) {
                                $type = MediaTypeEnum::getAsOptions()[$record->mediable_type];

                                return sprintf('%s (%s)', $record->name, $type);
                            })
                            ->searchable(['name'])
                            ->preload(),
                    ]),
            ])
            ->headerActions([
                ActionTable::make('is_1440p')
                    ->label('Show Pixel In 1440p')
                    ->icon('heroicon-o-computer-desktop')
                    ->url(request()->url() . '?is_1440p=true')
                    ->visible(!request()->filled('is_1440p')),
                ActionTable::make('is_1080p')
                    ->label('Show Pixel In 1080p')
                    ->icon('heroicon-o-computer-desktop')
                    ->url(request()->url())
                    ->visible(request()->filled('is_1440p')),
            ])
            ->paginated(false)
            ->bulkActions([
                // ...
            ]);
    }

    public function runningTextAction(): Action
    {
        return Action::make('runningTextAction')
            ->label('Running Text Configuration')
            ->color('info')
            ->mountUsing(function (Form $form) {
                $form->fill($this->record->attributesToArray());
            })
            ->form([
                Forms\Components\Toggle::make('running_text_is_include')->label('With Running Text?')->live(),
                Forms\Components\Select::make('running_text_position')->label('Running Text Position')
                    ->options(['bottom' => 'Bottom', 'top' => 'Top'])
                    ->visible(fn(Forms\Get $get): bool => $get('running_text_is_include') == 'true'),
                Forms\Components\Select::make('running_text_id')
                    ->label('Running Text')
                    ->options(RunningText::all()->pluck('name', 'id'))
                    ->visible(fn(Forms\Get $get): bool => $get('running_text_is_include') == 'true')
                    ->required(fn(Forms\Get $get): bool => $get('running_text_is_include') == 'true')
                    ->searchable(),
            ])
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->action(function ($data) {
                $this->record->update($data);
                Notification::make()
                    ->title('Running text updated.')
                    ->success()
                    ->send();
            });
    }

    public function previewAction(): Action
    {
        Log::info($this->record->id);
        return Action::make('previewAction')
            ->label('Preview')
            ->color('success')
            ->icon('heroicon-o-eye')
            ->url('/layout/' . $this->record->id)
            ->openUrlInNewTab();
    }

    public function render()
    {
        return view('livewire.layout-spots');
    }
}
