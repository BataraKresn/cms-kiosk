<?php

namespace App\Filament\Resources;

use App\Enums\MediaTypeEnum;
use App\Filament\Resources\MediaResource\Pages;
use App\Forms\Components\SelectMedia;
use App\Models\Media;
use App\Models\MediaHls;
use App\Models\MediaHtml;
use App\Models\MediaImage;
use App\Models\MediaLiveUrl;
use App\Models\MediaQrCode;
use App\Models\MediaSlider;
use App\Models\MediaVideo;
use App\Models\Spot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use stdClass;
use App\Exports\MediaExport;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
 

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Management';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        SelectMedia::make('mediable')
                            ->label('Choose Media')
                            ->types([
                                Forms\Components\MorphToSelect\Type::make(MediaImage::class)->titleAttribute('name'),
                                Forms\Components\MorphToSelect\Type::make(MediaVideo::class)->titleAttribute('name'),
                                // Forms\Components\MorphToSelect\Type::make(MediaQrCode::class)->titleAttribute('name'),
                                Forms\Components\MorphToSelect\Type::make(MediaHtml::class)->titleAttribute('name'), // iframe and slider html
                                Forms\Components\MorphToSelect\Type::make(MediaHls::class)->titleAttribute('name'),
                                Forms\Components\MorphToSelect\Type::make(MediaLiveUrl::class)->titleAttribute('name'),
                                Forms\Components\MorphToSelect\Type::make(MediaSlider::class)->titleAttribute('name'),
                            ])
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\Textarea::make('description')->required(),
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
                Tables\Columns\TextColumn::make('name')->label('Media')->searchable(),
                Tables\Columns\TextColumn::make('mediable.name')->label('Content'),
                Tables\Columns\TextColumn::make('mediable_type')->label('Type')->formatStateUsing(fn(string $state): string => MediaTypeEnum::getAsOptions()[$state]),
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
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
        }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->label('Media')->searchable(),
    //             Tables\Columns\TextColumn::make('mediable.name')->label('Content'),
    //             Tables\Columns\TextColumn::make('mediable_type')->label('Type')->formatStateUsing(fn(string $state): string => MediaTypeEnum::getAsOptions()[$state]),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make()
    //                 ->visible(fn(Media $record) => $record->id != Media::NO_MEDIA)
    //                 ->after(function (Media $record) {
    //                     Spot::where('media_id', $record->id)->update(['media_id' => Media::NO_MEDIA]);
    //                 }),
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
