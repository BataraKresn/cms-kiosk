<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaHtmlResource\Pages;
use App\Models\MediaHtml;
use Filament\Forms;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use stdClass;
use Str;

class MediaHtmlResource extends Resource
{
    protected static ?string $model = MediaHtml::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Media';

    protected static bool $shouldRegisterNavigation = false;

    public static function formSchema()
    {
        // return [
        //     Forms\Components\TextInput::make('name')->required(),
        //     // Forms\Components\TextInput::make('slug'),
        //     Forms\Components\FileUpload::make('path')->required()
        //         ->label('HTML File')
        //         ->required()
        //         ->downloadable()
        //         ->previewable(false)
        //         ->acceptedFileTypes(['text/html'])
        //         ->helperText('* Allowed file type only HTML')
        //         ->disk('minio')
        //         ->directory('html')
        //         ->visibility('public')
        //     ];

        return [
            Forms\Components\TextInput::make('name')->required(),
            // Forms\Components\TextInput::make('slug'),
            Forms\Components\FileUpload::make('path')->required()
                ->getUploadedFileNameForStorageUsing(
                    fn (TemporaryUploadedFile $file): string => (string) str(Str::lower($file->getClientOriginalName()))
                        ->prepend('html-'),
                )
                ->downloadable()
                ->previewable(false)
                ->acceptedFileTypes(['text/html'])
                ->helperText('* Allowed file type only HTML'),
            ToggleButtons::make('url')
                ->label(fn (?MediaHtml $record) => $record 
                    ? new HtmlString('<a href="' . env('URL_IMAGE') . '/storage/' . $record->path . '" target="_blank" style="color:blue;">Preview HTML</a>') 
                    : '')
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
                }),                Tables\Columns\TextColumn::make('name')
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
            'index' => Pages\ListMediaHtmls::route('/'),
            'create' => Pages\CreateMediaHtml::route('/create'),
            'edit' => Pages\EditMediaHtml::route('/{record}/edit'),
        ];
    }
}
