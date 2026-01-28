<?php

namespace App\Filament\Resources;

use App\Enums\AnimationTypeEnum;
use App\Filament\Resources\MediaSliderResource\Pages;
use App\Filament\Resources\MediaSliderResource\RelationManagers;
use App\Models\MediaSlider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use stdClass;

class MediaSliderResource extends Resource
{
    protected static ?string $model = MediaSlider::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $navigationLabel = 'Slider';

    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function formSchema()
    {
        return [
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('animation_type')->options(AnimationTypeEnum::getAsOptions())->required(),
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
                Tables\Columns\TextColumn::make('animation_type')->label('Animation')->searchable(),
                Tables\Columns\TextColumn::make('media_slider_contents_count')->counts('media_slider_contents')->label('Total Content'),
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
    //             Tables\Columns\TextColumn::make('animation_type')->label('Animation')->searchable(),
    //             Tables\Columns\TextColumn::make('media_slider_contents_count')->counts('media_slider_contents')->label('Total Content'),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
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
            RelationManagers\MediaSliderContentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaSliders::route('/'),
            'create' => Pages\CreateMediaSlider::route('/create'),
            'edit' => Pages\EditMediaSlider::route('/{record}/edit'),
        ];
    }
}
