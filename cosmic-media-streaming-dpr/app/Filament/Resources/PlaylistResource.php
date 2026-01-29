<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaylistResource\Pages;
use App\Models\Playlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use stdClass;
use App\Filament\Traits\OptimizeQueries;
use Illuminate\Database\Eloquent\Builder;

class PlaylistResource extends Resource
{
    use OptimizeQueries;
    protected static ?string $model = Playlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-pause';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('playlist_count', 60, function () {
            return static::getModel()::count();
        });
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('playlist_layouts');
    }

    public static function formSchema()
    {
        return [
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Textarea::make('description')->required(),
            Forms\Components\Checkbox::make('is_all_day')->label('Is All Day?'),
            // Forms\Components\TextInput::make('layout_interval')->default(0)->numeric()->hint('In Minutes'),
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
                Tables\Columns\TextColumn::make('playlist_layouts_count')->counts('playlist_layouts')->label('Total Layout'),                // Created_at column with sorting enabled
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Setup Playlist')
                    ->icon('heroicon-o-computer-desktop')
                    ->url(fn(Playlist $record): string => route('filament.back-office.resources.playlists.assign.layout', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc')->modifyQueryUsing(fn($query) => $query->withCount('playlist_layouts'));
    }


    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             Tables\Columns\TextColumn::make('playlist_layouts_count')->counts('playlist_layouts')->label('Total Layout'),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Action::make('Setup Playlist')
    //                 ->icon('heroicon-o-computer-desktop')
    //                 ->url(fn (Playlist $record): string => route('filament.back-office.resources.playlists.assign.layout', $record)),
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
            'index' => Pages\ListPlaylists::route('/'),
            'create' => Pages\CreatePlaylist::route('/create'),
            'edit' => Pages\EditPlaylist::route('/{record}/edit'),
            'assign.layout' => Pages\AssignLayout::route('/{record}/assign-layout'),
        ];
    }
}
