<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RemoteResource\Pages;
use App\Filament\Resources\RemoteResource\RelationManagers;
use App\Models\Remote;
use Exception;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;

class RemoteResource extends Resource
{
    protected static ?string $model = Remote::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Device Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Device Name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('device_identifier')
                            ->label('Device ID')
                            ->disabled()
                            ->helperText('Auto-generated from Android device'),
                        Forms\Components\TextInput::make('token')
                            ->label('Authentication Token')
                            ->disabled()
                            ->helperText('Used for WebSocket authentication'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Remote Control Settings')
                    ->schema([
                        Forms\Components\Toggle::make('remote_control_enabled')
                            ->label('Enable Remote Control')
                            ->helperText('Allow this device to be controlled remotely')
                            ->default(false),
                        Forms\Components\TextInput::make('remote_control_port')
                            ->label('Control Port')
                            ->numeric()
                            ->default(5555)
                            ->disabled(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Device Status')
                    ->schema([
                        Forms\Components\TextInput::make('status')
                            ->disabled(),
                        Forms\Components\TextInput::make('android_version')
                            ->label('Android Version')
                            ->disabled(),
                        Forms\Components\TextInput::make('app_version')
                            ->label('APK Version')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        Forms\Components\TextInput::make('battery_level')
                            ->label('Battery %')
                            ->disabled()
                            ->suffix('%'),
                        Forms\Components\TextInput::make('last_seen_at')
                            ->label('Last Seen')
                            ->disabled(),
                    ])->columns(3),
                
                Forms\Components\Section::make('Legacy Settings')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Legacy VNC URL')
                            ->url()
                            ->helperText('Old VNC connection URL (deprecated)'),
                    ])->collapsed(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no.')
                    ->label('No.')
                    ->getStateUsing(function (stdClass $rowLoop, $record) {
                        // Get current page and per-page size using Laravel's built-in pagination
                        $page = request()->get('page', 1); // Default to page 1
                        $perPage = request()->get('per_page', 10); // Default pagination size
    
                        // Calculate row number based on current page and per-page size
                        return ($page - 1) * $perPage + $rowLoop->iteration;
                    }),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('status')->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(),
            ])
            ->filters([
                // Your filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('remoteControl')
                    ->label('Remote Control')
                    ->icon('heroicon-o-tv')
                    ->color('success')
                    ->url(fn (Remote $record): string => route('filament.admin.resources.remotes.remote-control-viewer', ['record' => $record->id]))
                    ->visible(fn (Remote $record): bool => $record->remote_control_enabled && $record->status === 'Connected')
                    ->openUrlInNewTab(),
                Action::make('legacyRemoteControl')
                    ->label('Legacy VNC')
                    ->icon('gmdi-cast-connected-o')
                    ->color('info')
                    ->url(function (Remote $record) {
                        Log::info($record->url);
                        return $record->url;
                    })->openUrlInNewTab()
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateHeading('No Devices Registered')
            ->emptyStateDescription('Devices will appear here automatically when the Android APK is installed and registers with the system.')
            ->emptyStateIcon('heroicon-o-device-phone-mobile')
            ->defaultSort('created_at', 'desc'); // Sort by created_at by default
    }
    
    
    /**
     * Helper method to get HTTP status code of a URL.
     *
     * @param string $url
     * @return int|null
     */
    // protected static function getHttpStatusCode(string $url): ?int
    // {
    //     try {
    //         $headers = get_headers($url, 1);
    
    //         if ($headers && isset($headers[0])) {
    //             preg_match('/HTTP\/\d+\.\d+ (\d+)/', $headers[0], $matches);
    //             return isset($matches[1]) ? (int)$matches[1] : null;
    //         }
    //     } catch (Exception $e) {
    //         Log::error('Error fetching URL status: ' . $e->getMessage());
    //     }
    
    //     return null; // Return null if unable to fetch the status
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
            'index' => Pages\ListRemotes::route('/'),
            'edit' => Pages\EditRemote::route('/{record}/edit'),
            'remote-control-viewer' => Pages\RemoteControlViewer::route('/{record}/remote-control'),
        ];
    }
}
