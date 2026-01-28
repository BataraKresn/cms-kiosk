<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaVideoResource\Pages;
use App\Models\MediaVideo;
use Filament\Forms;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Str;
use stdClass;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Jobs\ConvertVideoToLowBitrate; // Import Job
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;

class MediaVideoResource extends Resource
{
    protected static ?string $model = MediaVideo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Media';

    protected static bool $shouldRegisterNavigation = false;

    public static function formSchema()
    {
        return [
            TextInput::make('name')
                ->label('Video Name')
                ->required(),

            FileUpload::make('path')
                ->label('Upload Video')
                ->required()
                ->downloadable()
                ->previewable(false)
                ->acceptedFileTypes(['video/mp4'])
                ->helperText('* Allowed file type is MP4')
                ->disk('minio')
                ->directory('videos')
                ->visibility('public')
                ->afterStateUpdated(function ($state) {
                    if ($state && is_string($state)) {
                        ConvertVideoToLowBitrate::dispatch($state);
                    }
                }),
        ];
    }

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make()
    //                 ->schema([
    //                     Grid::make(1) // Ensures full-width layout
    //                         ->schema([
    //                             TextInput::make('name')
    //                                 ->label('Video Name')
    //                                 ->required()
    //                                 ->columnSpanFull(), // Makes input full width

    //                             FileUpload::make('path')
    //                                 ->label('Upload Video')
    //                                 ->downloadable()
    //                                 ->previewable(false)
    //                                 ->acceptedFileTypes(['video/mp4'])
    //                                 ->helperText('* Allowed file type is MP4')
    //                                 ->afterStateUpdated(function ($state) {
    //                                     if ($state && is_string($state)) {
    //                                         ConvertVideoToLowBitrate::dispatch($state);
    //                                     }
    //                                 }),

    //                             ToggleButtons::make('url')
    //                                 ->label(fn(?MediaVideo $record) => $record
    //                                     ? new HtmlString('<a href="' . env('URL_IMAGE') . '/storage/' . $record->path . '" target="_blank" style="color:blue;">Preview Video</a>')
    //                                     : '')
    //                                 ->columnSpanFull(), // Makes input full width
    //                         ]),
    //                 ])
    //                 ->columnSpanFull(), // Ensures section is full width
    //         ]);
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                // Created_at column with sorting enabled
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),

            ])
            ->actions([
                Action::make('Edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')  // Corrected icon to match the Filament icon set
                ->color('warning') // Set the color to the custom hex value
                ->action(function (MediaVideo $record) {
                    // return redirect()->route('custom-template', ['id' => $record->id]);
                    return redirect()->away(env('URL_APP') . "/back-office/media-videos/editMediaVideo/{$record->id}");
                }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaVideos::route('/'),
            'create' => Pages\CreateMediaVideo::route('/create'),
        ];
    }
}
