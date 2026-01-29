<?php

namespace App\Filament\Resources;

use App\Enums\AnimationTypeEnum;
use App\Filament\Resources\LayoutResource\Pages;
use App\Models\Layout;
use App\Models\PlaylistLayout;
use App\Models\Screen;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use stdClass;
use App\Filament\Traits\OptimizeQueries;
use Illuminate\Database\Eloquent\Builder;

class LayoutResource extends Resource
{
    use OptimizeQueries;

    protected static ?string $model = Layout::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['screen', 'spots']);
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }
    public static function formSchema()
    {
        $Now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
        return [
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Hidden::make('is_template')
            ->default(1),
            Forms\Components\Hidden::make('created_at')
            ->default($Now->format('Y-m-d H:i:s')),
            Forms\Components\Select::make('screen_id')
            ->label('Select Screen')
            ->options(Screen::whereIn('id', [1, 2])->pluck('name', 'id'))
            ->required(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
                ->schema([
                    Forms\Components\Section::make()
                        ->columns(2)
                        ->schema(static::formSchema()),
                    ]);
    }

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //                 Forms\Components\TextInput::make('name')->label('Layout Name')->required()->label('Layout'),
    //         ])->columns(1);
    // }

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
                Tables\Columns\TextColumn::make('name')->label('Layout Name')->searchable(),
                Tables\Columns\TextColumn::make('screen.name')->label('Screen'),
                // Created_at column with sorting enabled
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                    ReplicateAction::make()
                        ->beforeReplicaSaved(function (Model $replica): void {
                            $replica->name = sprintf('CLONE [%s]', $replica->name);
                            $replica->is_template = false;
                        })
                        ->after(function (Model $replica, Layout $record): void {
                            foreach ($record->spots as $spot) {
                                $newSpot = $spot->replicate();
                                $replica->spots()->save($newSpot);
                            }
                        })
                        ->label('Clone')
                        ->color('gray')
                        ->successNotificationTitle('Layout cloned')
                        ->visible(fn (Layout $record) => $record->is_template),
            
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Layout $record) => !$record->is_template),
            
                    Action::make('Assign Media')
                        ->label('Assign Media')
                        ->icon('heroicon-o-cursor-arrow-ripple')
                        ->color('info')
                        ->url(fn (Layout $record): string => route('filament.back-office.resources.layouts.assign_media', $record))
                        ->visible(fn (Layout $record) => !$record->is_template),
            
                    Action::make('View Template')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Layout $record): string => route('filament.back-office.resources.layouts.view_spot', $record))
                        ->visible(fn (Layout $record) => $record->is_template),
            
                    Action::make('Editor')
                        ->label('Editor')
                        ->icon('heroicon-o-pencil')  // Corrected icon to match the Filament icon set
                        ->color('warning') // Set the color to the custom hex value
                        ->action(function (Layout $record) {
                            // return redirect()->route('custom-template', ['id' => $record->id]);
                            return redirect()->away(env('URL_APP') . "/custom-layout?id={$record->id}");
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->color('danger'),
            ])
            
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }


    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->label('Layout Name')->searchable(),
    //             Tables\Columns\TextColumn::make('screen.name')->label('Screen'),
    //         ])
    //         ->actions([
    //             ReplicateAction::make()
    //                             ->beforeReplicaSaved(function (Model $replica): void {
    //                                 $replica->name = sprintf('CLONE [%s]', $replica->name);
    //                                 $replica->is_template = false;
    //                             })->after(function (Model $replica, Layout $record): void {
    //                                 foreach ($record->spots as $spot) {
    //                                     $newSpot = $spot->replicate();
    //                                     $replica->spots()->save($newSpot);
    //                                 }
    //                             })
    //                             ->label('Clone')
    //                             ->successNotificationTitle('Layout cloned')
    //                             ->visible(fn (Layout $record) => $record->is_template),

    //             Tables\Actions\EditAction::make()->visible(fn (Layout $record) => !$record->is_template),

    //             Action::make('Assign Media')
    //                     ->label('Assign Media')
    //                     ->icon('heroicon-o-cursor-arrow-ripple')
    //                     ->url(fn (Layout $record): string => route('filament.back-office.resources.layouts.assign_media', $record))->visible(fn (Layout $record) => !$record->is_template),

    //             Action::make('View Template')
    //                     ->label('View')
    //                     ->icon('heroicon-o-eye')
    //                     ->url(fn (Layout $record): string => route('filament.back-office.resources.layouts.view_spot', $record))->visible(fn (Layout $record) => $record->is_template),

    //             Tables\Actions\DeleteAction::make()->visible(fn (Layout $record) => !$record->is_template)
    //                     ->after(function (Layout $record) {
    //                         PlaylistLayout::where('layout_id', $record->id)->forceDelete();
    //                     }),
    //         ])
    //         ->bulkActions([
    //             // Tables\Actions\BulkActionGroup::make([
    //                 // Tables\Actions\DeleteBulkAction::make(),
    //             // ]),
    //         ])
    //         ->emptyStateActions([
    //             Tables\Actions\CreateAction::make(),
    //         ])
    //         ->defaultSort('is_template', 'desc');
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
            'index' => Pages\ListLayouts::route('/'),
            'create' => Pages\CreateLayout::route('/create'),
            'edit' => Pages\EditLayout::route('/{record}/edit'),
            'delete' => Pages\EditLayout::route('/{record}/delete'),
            'assign_media' => Pages\AssignMedia::route('/{record}/assign-media'),
            'view_spot' => Pages\ViewSpot::route('/{record}/view-spot'),
        ];
    }
}
