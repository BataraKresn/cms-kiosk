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
use App\Filament\Traits\OptimizeQueries;

class RemoteResource extends Resource
{
    use OptimizeQueries;
    
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
                        $page = request()->get('page', 1);
                        $perPage = request()->get('per_page', 10);
                        return ($page - 1) * $perPage + $rowLoop->iteration;
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('Device Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'Connected' => 'heroicon-o-check-circle',
                        'Disconnected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Connected' => 'success',
                        'Disconnected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('battery_level')
                    ->label('Battery')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '-')
                    ->color(fn ($state) => match(true) {
                        !$state => 'gray',
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'danger',
                        default => 'gray'
                    })
                    ->icon(fn ($state) => match(true) {
                        !$state => 'heroicon-o-battery-0',
                        $state >= 80 => 'heroicon-o-battery-100',
                        $state >= 50 => 'heroicon-o-battery-50',
                        default => 'heroicon-o-battery-0'
                    }),  
                Tables\Columns\TextColumn::make('wifi_strength')
                    ->label('WiFi')
                    ->formatStateUsing(fn ($state) => $state ? "$state dBm" : 'N/A')
                    ->color(fn ($state) => match(true) {
                        $state >= -50 => 'success',
                        $state >= -70 => 'warning',
                        default => 'danger'
                    }),
                Tables\Columns\TextColumn::make('network_type')
                    ->label('Network')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'WiFi' => 'success',
                        'Mobile' => 'warning',
                        'Ethernet' => 'info',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('ram_usage_mb')
                    ->label('RAM')
                    ->formatStateUsing(fn ($state, $record) => 
                        $state && $record->ram_total_mb 
                            ? sprintf('%d / %d MB', $state, $record->ram_total_mb)
                            : 'N/A'
                    ),
                Tables\Columns\TextColumn::make('storage_available_mb')
                    ->label('Storage')
                    ->formatStateUsing(fn ($state) => 
                        $state ? sprintf('%.1f GB', $state / 1024) : 'N/A'
                    ),
                Tables\Columns\IconColumn::make('screen_on')
                    ->label('Screen')
                    ->boolean()
                    ->trueIcon('heroicon-o-sun')
                    ->falseIcon('heroicon-o-moon')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('cpu_temp')
                    ->label('CPU')
                    ->formatStateUsing(fn ($state) => $state ? sprintf('%.1f°C', $state) : 'N/A')
                    ->color(fn ($state) => match(true) {
                        $state < 50 => 'success',
                        $state < 70 => 'warning',
                        default => 'danger'
                    }),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Show Deleted')
                    ->placeholder('Active Devices')
                    ->trueLabel('Only Deleted')
                    ->falseLabel('With Deleted')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Remote $record): bool => $record->trashed()),
                Tables\Actions\RestoreAction::make()
                    ->label('Restore')
                    ->successNotificationTitle('Device restored'),
                Tables\Actions\DeleteAction::make()
                    ->label('Soft Delete')
                    ->requiresConfirmation()
                    ->modalHeading('Soft Delete Device')
                    ->modalDescription('Device will be hidden but data retained. Device can re-register with same ID.')
                    ->successNotificationTitle('Device soft deleted'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Permanent Delete')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Device')
                    ->modalDescription('This will PERMANENTLY delete the device and all related data. This action cannot be undone!')
                    ->successNotificationTitle('Device permanently deleted'),
                Action::make('remoteControl')
                    ->label('Remote Control')
                    ->icon('heroicon-o-tv')
                    ->color('success')
                    ->url(fn (Remote $record): string => route('filament.admin.resources.remotes.remote-control-viewer', ['record' => $record->id]))
                    ->visible(fn (Remote $record): bool => !$record->trashed() && $record->remote_control_enabled && $record->status === 'Connected')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Soft Delete Selected'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Permanently Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Devices')
                        ->modalDescription('This will PERMANENTLY delete all selected devices. This cannot be undone!'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore Selected'),
                ]),
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
    
    public static function canCreate(): bool
    {
        return false; // Disable manual creation, devices auto-register via APK
    }
}
