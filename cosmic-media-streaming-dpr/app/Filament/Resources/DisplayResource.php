<?php

namespace App\Filament\Resources;

use App\Enums\DisplayTypeEnum;
use App\Enums\OperatingSystemEnum;
use App\Events\DisplayReloadEvent;
use App\Filament\Resources\DisplayResource\Pages;
use App\Models\Display;
use App\Models\Schedule;
use App\Models\Screen;
use ArberMustafa\FilamentLocationPickrField\Forms\Components\LocationPickr;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;
use App\Filament\Traits\OptimizeQueries;
use Illuminate\Database\Eloquent\Builder;

class DisplayResource extends Resource
{
    use OptimizeQueries;

    protected static ?string $model = Display::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['schedule', 'screen']);
    }

    public static function generalSchema()
    {
        return [
            Forms\Components\Section::make()->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('token')->required()->disabled()->required(),
                ]),
            Forms\Components\Section::make()->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\Select::make('screen_id')
                        ->label('Select Screen')
                        ->options(Screen::whereIn('id', [1, 2])->pluck('name', 'id'))
                        ->required(),
                    Forms\Components\Select::make('display_type')->options(DisplayTypeEnum::getAsOptions())->required()->label('Display Type')->default(DisplayTypeEnum::OTHER),
                    Forms\Components\Select::make('operating_system')->options(OperatingSystemEnum::getAsOptions())->required()->label('Operating System')->default(OperatingSystemEnum::ANDROID),
                ]),
            Forms\Components\Section::make()->columns(1)
                ->schema([
                    Forms\Components\Select::make('schedule_id')->label('Schedule')->options(Schedule::all()->pluck('name', 'id'))->searchable()->required(),
                ]),
        ];
    }

    public static function locationSchema()
    {
        return [
            Forms\Components\Section::make()->columns(1)
                ->schema([
                    LocationPickr::make('location')
                        ->mapControls([
                            'mapTypeControl'    => false,
                            'scaleControl'      => false,
                            'streetViewControl' => false,
                            'rotateControl'     => false,
                            'fullscreenControl' => false,
                            'searchBoxControl'  => true,
                            'zoomControl'       => false,
                        ])
                        ->defaultZoom(14)
                        ->draggable()
                        ->clickable()
                        ->height('40vh')
                        ->defaultLocation([-6.211607394994271, 106.79747683599052]),
                ]),
            Forms\Components\Section::make()->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('location_description'),
                    Forms\Components\TextInput::make('group'),
                ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Display')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->icon('heroicon-o-cog')
                            ->schema(static::generalSchema()),
                        Tabs\Tab::make('Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema(static::locationSchema()),
                    ])
                    ->persistTabInQueryString()
                    ->contained(true),
            ])->columns(1);
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
                Tables\Columns\TextColumn::make('schedule.name')->searchable(),
                Tables\Columns\TextColumn::make('screen.name')->searchable(),
                Tables\Columns\TextColumn::make('token')->label('Token')
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('Token copied')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Refresh Display')
                    ->label('Refresh Device')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (Display $record) {
                        // Send the HTTP request and capture the response
                        $urlAPI = env('URL_PDF');
                        
                        // Check if URL_PDF is set in environment
                        if (empty($urlAPI)) {
                            Log::error('URL_PDF environment variable is not set');
                            Notification::make()
                                ->title('Error')
                                ->body('URL_PDF environment variable is not set. Please configure it in your .env file.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Ensure URL has protocol
                        if (!preg_match('/^https?:\/\//', $urlAPI)) {
                            $urlAPI = 'https://' . $urlAPI;
                        }
                        
                        // Remove trailing slash if present
                        $urlAPI = rtrim($urlAPI, '/');
                        
                        Log::info('URL API: ' . $urlAPI);
                        $url = $urlAPI . '/send_refresh_device?token=' . $record->token;
                        Log::info('Full Request URL: ' . $url);
                        
                        try {
                            $response = Http::timeout(30)->get($url);
                            
                            // Log the response for debugging
                            if ($response->successful()) {
                                Log::info('Refresh Device response:', ['response' => $response->body()]);
                                
                                Notification::make()
                                    ->title('Success')
                                    ->body('Device refresh command sent successfully.')
                                    ->success()
                                    ->send();
                            } else {
                                Log::error('Failed to refresh device:', [
                                    'status' => $response->status(),
                                    'response' => $response->body()
                                ]);
                                
                                Notification::make()
                                    ->title('Error')
                                    ->body('Failed to refresh device: HTTP ' . $response->status())
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Log::error('Exception during device refresh:', [
                                'message' => $e->getMessage(),
                                'url' => $url
                            ]);
                            
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to refresh device: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(Display $record) => !is_null($record->schedule_id)),
                Action::make('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Display $record): string => route('display-token', $record->token))
                    ->openUrlInNewTab()
                    ->visible(fn(Display $record) => !is_null($record->schedule_id)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             Tables\Columns\TextColumn::make('schedule.name')->searchable(),
    //             Tables\Columns\TextColumn::make('screen.name')->searchable(),
    //             Tables\Columns\TextColumn::make('token')->label('Token')
    //                 ->limit(40)
    //                 ->copyable()
    //                 ->copyMessage('Token copied')
    //                 ->copyMessageDuration(1500),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             /* list of actions */

    //             ActionGroup::make([
    //                 Action::make('Preview')
    //                     ->icon('heroicon-o-eye')
    //                     ->url(fn(Display $record): string => route('display-token', $record->token))
    //                     ->openUrlInNewTab()
    //                     ->visible(fn(Display $record) => !is_null($record->schedule_id)),
    //                 Tables\Actions\EditAction::make(),
    //             ]),

    //         ])
    //         ->bulkActions([
    //             // Tables\Actions\BulkActionGroup::make([
    //             //     Tables\Actions\DeleteBulkAction::make(),
    //             // ]),
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
            'index' => Pages\ListDisplays::route('/'),
            'create' => Pages\CreateDisplay::route('/create'),
            'edit' => Pages\EditDisplay::route('/{record}/edit'),
        ];
    }
}
