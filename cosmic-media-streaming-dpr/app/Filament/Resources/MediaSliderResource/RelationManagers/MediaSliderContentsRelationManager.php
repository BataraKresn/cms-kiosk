<?php

namespace App\Filament\Resources\MediaSliderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Str;

class MediaSliderContentsRelationManager extends RelationManager
{
    protected static string $relationship = 'media_slider_contents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\FileUpload::make('path')->label('Image/HTML/Video')->required()
                            ->optimize('webp')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => (string) str(Str::lower($file->getClientOriginalName()))
                                    ->prepend('attachment-'),
                            )
                            ->downloadable()
                            ->previewable(true)
                            ->acceptedFileTypes(['image/jpg', 'image/png', 'image/jpeg', 'text/html', 'video/mp4'])
                            ->helperText('* Allowed file type are JPG, JPEG, PNG, HTML and Video (MP4)')
                            ->afterStateUpdated(fn (callable $set, $state) => $set('mime', $state?->getMimeType())),
                        Forms\Components\TextInput::make('mime')->maxLength(25)->readonly(),
                        Forms\Components\TextInput::make('duration')->required()->numeric()->label('Duration (In secs)')->default(5000),
                    ]),
                ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('duration')->label('Duration (In secs)'),
                Tables\Columns\TextColumn::make('mime')->label('Mime Type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Content')->createAnother(false),
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
            ->paginated(false)
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('sort', 'asc')
            ->reorderable('sort');

    }
}
