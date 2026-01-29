<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaImageResource\Pages;
use App\Models\MediaImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use stdClass;
use Str;
use App\Filament\Traits\OptimizeQueries;

class MediaImageResource extends Resource
{
    use OptimizeQueries;
    
    protected static ?string $model = MediaImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Media';

    protected static bool $shouldRegisterNavigation = false;

    public static function formSchema()
    {
        // return [
        //     Forms\Components\TextInput::make('name')->required(),
        //     Forms\Components\FileUpload::make('path')
        //         ->label('Image File')
        //         ->required()
        //         ->downloadable()
        //         ->previewable()
        //         ->acceptedFileTypes(['image/jpg', 'image/png', 'image/jpeg'])
        //         ->helperText('* Allowed file types are JPG, JPEG, and PNG')
        //         ->disk('minio')  // Make sure this disk is configured
        //         ->directory('image')  // Path to store the file in MinIO
        //         ->visibility('public')// Ensure MinIO allows 'private' visibility
        //         ];

        return [
            Forms\Components\TextInput::make('name')->required(),
            // Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\FileUpload::make('path')
                ->label('Image File')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->optimize('webp')  // Optimize to WebP format for better performance
                ->getUploadedFileNameForStorageUsing(
                    fn(TemporaryUploadedFile $file): string => (string) str(Str::lower($file->getClientOriginalName()))
                        ->prepend('attachment-'),
                )
                ->downloadable()
                ->previewable(true)
                ->acceptedFileTypes(['image/jpg', 'image/png', 'image/jpeg'])
                ->helperText('* Allowed file type are JPG, JPEG and PNG')
                ->disk('minio')
                ->directory('images')
                ->visibility('public'),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
            'index' => Pages\ListMediaImages::route('/'),
            'create' => Pages\CreateMediaImage::route('/create'),
            'edit' => Pages\EditMediaImage::route('/{record}/edit'),
        ];
    }
}
