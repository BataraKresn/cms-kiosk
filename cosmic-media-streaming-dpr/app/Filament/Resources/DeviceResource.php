<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Device;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use stdClass;

// class DeviceResource extends Resource
// {
//     protected static ?string $model = Device::class;
//     protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
//     protected static ?string $navigationGroup = 'Management';
//     protected static ?int $navigationSort = 5;

//     public static function getNavigationBadge(): ?string
//     {
//         return static::getModel()::count();
//     }

//     public static function table(Table $table): Table
//     {
//         return $table
//             ->columns([
//                 Tables\Columns\TextColumn::make('no.')
//                 ->label('No.')
//                 ->getStateUsing(function (stdClass $rowLoop, $record) {
//                     // Get current page and per-page size
//                     $page = request()->get('page', 1); // Default to page 1
//                     $perPage = request()->get('per_page', 10); // Default pagination size

//                     // Calculate row number based on current page and per-page size
//                     return ($page - 1) * $perPage + $rowLoop->iteration;
//                 }),  
//                 Tables\Columns\TextColumn::make('name')->label('Name')->searchable(),             
//                 Tables\Columns\TextColumn::make('serial_device')->label('Serial Device')->searchable(),
//                 Tables\Columns\TextColumn::make('ip_device')->label('IP Device')->searchable(),
//                 Tables\Columns\TextColumn::make('port_device')->label('Port Device')->searchable(),
//                 Tables\Columns\TextColumn::make('status_device')->label('Status')->searchable(),
//                 Tables\Columns\TextColumn::make('created_at')->label('Created at')
//                 ->sortable()
//                 ->searchable(),
//             ])
//             ->filters([
//                 // Add filters if needed
//             ])
//             ->actions([
//                 ActionGroup::make([
//                     Action::make('Reconnect to device')
//                         ->label('Reconnect to Device')
//                         ->icon('heroicon-s-link')
//                         ->color('success')
//                         ->requiresConfirmation()
//                         ->modalHeading('Reconnect to Device')
//                         ->action(function (Device $record) {
//                             $id = $record->id; 

//                             $devices = Http::get(env('SERVICE_REMOTE_DEVICE').'/reconnect_device?id=' . $id)->json();

//                             if ($devices) {
//                                 Notification::make()
//                                     ->title('Reconnect device successfully')
//                                     ->success()
//                                     ->send();
//                             }
//                         })
//                         ->visible(true),

//                     Action::make('Disconnect from device')
//                         ->label('Disconnect from Device')
//                         ->icon('heroicon-s-link-slash')
//                         ->color('danger')
//                         ->requiresConfirmation()
//                         ->modalHeading('Disconnect from Device')
//                         ->action(function (Device $record) {
//                             $urlDevice = "{$record->ip_device}:{$record->port_device}";
//                             $devices = Http::get(env('SERVICE_REMOTE_DEVICE') . '/disconnect_device', [
//                                 'url_device' => $urlDevice
//                             ]);

//                             if ($devices) {
//                                 Notification::make()
//                                     ->title('Disconnect device successfully')
//                                     ->success()
//                                     ->send();
//                             }
//                         })
//                         ->visible(true),

//                         Action::make('Wake Display')
//                         ->label('Wake Device')
//                         ->icon('heroicon-o-sun')
//                         ->color('success')
//                         ->requiresConfirmation()
//                         ->modalHeading('Wake Display')
//                         ->action(function (Device $record) {
//                             try {
//                                 // Correct string interpolation for the device URL
//                                 $urlDevice = "{$record->ip_device}:{$record->port_device}";
                    
//                                 $response = Http::get(env('SERVICE_REMOTE_DEVICE') . '/on_device', [
//                                     'url_device' => $urlDevice
//                                 ]);
                    
//                                 if ($response->successful()) {
//                                     Notification::make()
//                                         ->title('Wakeup device successfully')
//                                         ->success()
//                                         ->send();
//                                 } else {
//                                     Notification::make()
//                                         ->title('Failed to wake device')
//                                         ->danger()
//                                         ->send();
//                                 }
//                             } catch (\Exception $e) {
//                                 Notification::make()
//                                     ->title('Error waking device')
//                                     ->danger()
//                                     ->send();
//                             }
//                         })
//                         ->visible(true),

//                     Action::make('Sleep Display')
//                         ->label('Sleep Device')
//                         ->icon('heroicon-o-moon')
//                         ->color('danger')
//                         ->requiresConfirmation()
//                         ->modalHeading('Sleep Display')
//                         ->action(function (Device $record) {
//                             try {
//                                 // Correct string interpolation for the device URL
//                                 $urlDevice = "{$record->ip_device}:{$record->port_device}";
                    
//                                 $response = Http::get(env('SERVICE_REMOTE_DEVICE') . '/off_device', [
//                                     'url_device' => $urlDevice
//                                 ]);
                    
//                                 if ($response->successful()) {
//                                     Notification::make()
//                                         ->title('Sleep device successfully')
//                                         ->success()
//                                         ->send();
//                                 } else {
//                                     Notification::make()
//                                         ->title('Failed to sleep device')
//                                         ->danger()
//                                         ->send();
//                                 }
//                             } catch (\Exception $e) {
//                                 Notification::make()
//                                     ->title('Error sleeping device')
//                                     ->danger()
//                                     ->send();
//                             }
//                         })
//                         ->visible(true),

//                         // Action::make('Remote Control Device')
//                         // ->label('Remote Control Device')
//                         // ->icon('heroicon-s-link')
//                         // ->color('info')
//                         // ->requiresConfirmation()
//                         // ->modalHeading('Remote Control Device')
//                         // ->action(function (Device $record) {
//                         //     $urlDevice = "{$record->ip_device}:{$record->port_device}";
//                         //     Log::info($urlDevice);
//                         //     $devices = Http::get(env('SERVICE_REMOTE_DEVICE') . '/remote_device?url_device=' . $urlDevice)->json();
//                         //     Log::info($devices);
//                         //     if ($devices) {
//                         //         Notification::make()
//                         //             ->title('Remote device successfully')
//                         //             ->success()
//                         //             ->send();
//                         //     }
//                         // })
//                         // ->visible(true), 

//                     Action::make('remoteControl')
//                         ->label('Remote Control Device')
//                         ->icon('gmdi-cast-connected-o')
//                         ->color('info')
//                         ->url(function (Device $record): string {
//                             // Encode the IP address and port to ensure proper URL formatting
//                             $ipDevice = urlencode($record->ip_device);
//                             $portDevice = urlencode($record->port_device);
                            
//                             // Construct the URL
//                             $response = "http://100.76.251.32:8999/#!action=stream&udid={$ipDevice}%3A{$portDevice}&player=mse&ws=ws%3A%2F%2F100.76.251.32%3A8999%2F%3Faction%3Dproxy-adb%26remote%3Dtcp%253A8886%26udid%3D{$ipDevice}%253A{$portDevice}";
//                             return $response;
//                         })
//                         ->openUrlInNewTab(),

//                         Action::make('Delete from device')
//                         ->label('Delete from device')
//                         ->icon('elemplus-delete')
//                         ->color('danger')
//                         ->requiresConfirmation()
//                         ->modalHeading('Delete from device')
//                         ->action(function (Device $record) {
//                             $id = $record->id; 

//                             $devices = Http::delete(env('SERVICE_REMOTE_DEVICE').'/delete_device?id=' . $id)->json();

//                             if ($devices) {
//                                 Notification::make()
//                                     ->title('Delete device successfully')
//                                     ->success()
//                                     ->send();
//                             }
//                         })
//                         ->visible(true),
                        
//                 ])
//             ])
//             ->bulkActions([
//                 // Add bulk actions if needed
//             ]);
//     }
//     public static function getPages(): array
//     {
//         return [
//             'index' => Pages\ListDevices::route('/'),
//             'new-connection' => Pages\CreateDevice::route('/new-connection'),
//             'edit' => Pages\EditDevice::route('/{record}/edit'),
//         ];
//     }
// }