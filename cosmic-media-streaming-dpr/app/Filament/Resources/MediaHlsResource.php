<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaHlsResource\Pages;
use App\Models\MediaHls;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use stdClass;

class MediaHlsResource extends Resource
{
    protected static ?string $model = MediaHls::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Media';
    protected static bool $shouldRegisterNavigation = false;

    public static function formSchema()
    {
        return [
            Forms\Components\TextInput::make('name')->required(),
            // Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\TextInput::make('url')->label('Live URL')->url()->placeholder('https://')->required()->helperText('* Example: https://dpr-ri.go.id/link.m3u8')
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
                Tables\Columns\TextColumn::make('url')->label('Live URL')->searchable(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaHls::route('/'),
            'create' => Pages\CreateMediaHls::route('/create'),
            'edit' => Pages\EditMediaHls::route('/{record}/edit'),
        ];
    }
}
