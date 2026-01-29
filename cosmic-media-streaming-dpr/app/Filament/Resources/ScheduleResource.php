<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use stdClass;
use App\Filament\Traits\OptimizeQueries;
use Illuminate\Database\Eloquent\Builder;

class ScheduleResource extends Resource
{
    use OptimizeQueries;
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('schedule_playlists');
    }

    public static function formSchema()
    {
        return [
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Textarea::make('description')->required(),
            Forms\Components\Checkbox::make('is_whole_week')->label('Is Whole Week?'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema(static::formSchema()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('no.')
                    ->label('No.')
                    ->getStateUsing(function (stdClass $rowLoop, $record) {
                        // Get current page and per-page size
                        $page = request()->get('page', 1); // Default to page 1
                        $perPage = request()->get('per_page', 10); // Default pagination size

                        // Calculate row number based on current page and per-page size
                        return ($page - 1) * $perPage + $rowLoop->iteration;
                    }),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('schedule_playlists_count')->counts('schedule_playlists')->label('Total Playlist'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Schedule $record): string => route('schedule-preview', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn(Schedule $record): bool => $record->schedule_playlists->count() > 0),
                Action::make('Setup Schedule')
                    ->icon('heroicon-o-play-pause')
                    ->url(fn(Schedule $record): string => route('filament.back-office.resources.schedules.assign.playlist', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             Tables\Columns\TextColumn::make('schedule_playlists_count')->counts('schedule_playlists')->label('Total Playlist'),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Action::make('Preview')
    //                 ->label('Preview')
    //                 ->icon('heroicon-o-eye')
    //                 ->url(fn (Schedule $record): string => route('schedule-preview', $record->id))
    //                 ->openUrlInNewTab()
    //                 ->visible(fn (Schedule $record): bool => $record->schedule_playlists->count() > 0),
    //             Tables\Actions\EditAction::make(),
    //             Action::make('Setup Schedule')
    //                 ->icon('heroicon-o-play-pause')
    //                 ->url(fn (Schedule $record): string => route('filament.back-office.resources.schedules.assign.playlist', $record)),

    //         ])
    //         ->bulkActions([
    //             // Tables\Actions\BulkActionGroup::make([
    //             //     Tables\Actions\DeleteBulkAction::make(),
    //             // ]),
    //         ]);
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
            'assign.playlist' => Pages\AssignPlaylist::route('/{record}/assign-playlist'),
        ];
    }
}
