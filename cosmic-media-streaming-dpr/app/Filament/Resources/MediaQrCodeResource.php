<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaQrCodeResource\Pages;
use App\Models\MediaQrCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Str;
use App\Filament\Traits\OptimizeQueries;

class MediaQrCodeResource extends Resource
{
    use OptimizeQueries;
    
    protected static ?string $model = MediaQrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Media';

    protected static bool $shouldRegisterNavigation = false;

    public static function formSchema()
    {
        return [
            Forms\Components\TextInput::make('name'),
            // Forms\Components\TextInput::make('slug'),
            Forms\Components\FileUpload::make('path')->label('QRCODE File')
                ->optimize('webp')
                ->getUploadedFileNameForStorageUsing(
                    fn (TemporaryUploadedFile $file): string => (string) str(Str::lower($file->getClientOriginalName()))
                        ->prepend('attachment-'),
                )
                ->downloadable()
                ->previewable(false)
                ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg']),
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
                Tables\Columns\TextColumn::make('name')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
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
            'index' => Pages\ListMediaQrCodes::route('/'),
            'create' => Pages\CreateMediaQrCode::route('/create'),
            'edit' => Pages\EditMediaQrCode::route('/{record}/edit'),
        ];
    }
}
