<?php

namespace App\Filament\Resources;

use App\Enums\DirectionEnum;
use App\Filament\Resources\RunningTextResource\Pages;
use App\Models\Layout;
use App\Models\RunningText;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use stdClass;
use App\Filament\Traits\OptimizeQueries;

class RunningTextResource extends Resource
{
    use OptimizeQueries;
    
    protected static ?string $model = RunningText::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Management';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function previewContent($get)
    {
        $text = $get('description') ? $get('description') : 'No Text Available';
        $direction = $get('direction') ? $get('direction') : 'right';
        $bgcolor = $get('background_color') ? $get('background_color') : 'red';
        $color = $get('text_color') ? $get('text_color') : 'white';

        return new HtmlString('<div style="text-align:center;"><marquee scrolldelay="1" style="color:' . $color . ';" width="100%" direction="' . $direction . '" bgcolor="' . $bgcolor . '">' . $text . '</marquee></div>');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')->required()->live(),
                        Forms\Components\Textarea::make('description')->required(),
                        // Forms\Components\TextInput::make('url')->url()->label('API URL (Optional)')->placeholder('https://'),
                    ]),
                Forms\Components\Section::make()->columns(2)
                    ->schema([
                        Forms\Components\Select::make('direction')->options(DirectionEnum::getAsOptions())->required()->live(),
                        Forms\Components\TextInput::make('speed')->numeric()->required()->live()->hint('In seconds')->default(5),
                        Forms\Components\ColorPicker::make('background_color')->required()->live()->default('#ff0000'),
                        Forms\Components\ColorPicker::make('text_color')->required()->live()->default('#000000'),
                    ]),
                Forms\Components\Section::make()->columns(1)
                    ->schema([
                        Forms\Components\Placeholder::make('preview')
                        ->helperText('Just show direction, background, text color only, the actual result preview on layout or display instead')
                        ->content(function (Get $get) {
                            return static::previewContent($get);
                        }),
                    ]),
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
                // Tables\Columns\TextColumn::make('description'),
                Tables\Columns\ColorColumn::make('background_color'),
                Tables\Columns\ColorColumn::make('text_color'),
                Tables\Columns\TextColumn::make('direction'),
                Tables\Columns\TextColumn::make('speed')->suffix(' Seconds'),
                // Tables\Columns\TextColumn::make('preview')->html()
                // Created_at column with sorting enabled
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc'); // Sort by created_at by default
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             // Tables\Columns\TextColumn::make('description'),
    //             Tables\Columns\ColorColumn::make('background_color'),
    //             Tables\Columns\ColorColumn::make('text_color'),
    //             Tables\Columns\TextColumn::make('direction'),
    //             Tables\Columns\TextColumn::make('speed')->suffix(' Seconds'),
    //             // Tables\Columns\TextColumn::make('preview')->html()
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make()->after(function (RunningText $record) {
    //                 Layout::where('running_text_id', $record->id)->update(['running_text_id' => null]);
    //             }),
    //         ])
    //         ->bulkActions([
    //             // Tables\Actions\BulkActionGroup::make([
    //             //     Tables\Actions\DeleteBulkAction::make(),
    //             // ]),
    //         ])
    //         ->emptyStateActions([
    //             Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListRunningTexts::route('/'),
            'create' => Pages\CreateRunningText::route('/create'),
            'edit' => Pages\EditRunningText::route('/{record}/edit'),
        ];
    }
}
