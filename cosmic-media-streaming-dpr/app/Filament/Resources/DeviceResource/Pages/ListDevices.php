<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Exception;

use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Create new connection')
                ->label('Create new connection')
                ->action(function (array $data) {
                    $apiUrl = env(key: 'SERVICE_REMOTE_DEVICE')."/new_connection_device";
                    $name = $data['name'];
                    $serialNumber = $data['serial_number'];
                    $url = $data['url'];
                    $port = $data['port'];


                    try {
                        $response = Http::timeout(10)->post($apiUrl, [
                            'name' => $name,
                            'serial_number' => $serialNumber,
                            'ip_device' => $url,
                            'port_device' => $port,
                        ]);

                        if ($response->successful()) {
                            Notification::make()
                                ->title('New Device connected')
                                ->success()
                                ->send();
                        } else {
                            $errorMessage = $this->getErrorMessage($response);
                            throw new Exception($errorMessage);
                        }
                    } catch (RequestException $e) {
                        $this->handleConnectionError($e);
                    } catch (Exception $e) {
                        $this->handleGeneralError($e);
                    }
                })
                ->steps([
                    Step::make('New Device Connection')
                        ->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('serial_number')
                                ->required(),
                            TextInput::make('url')
                                ->required(),
                            TextInput::make('port')
                                ->required(),
                        ]),
                    ]),
                ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->withColumns([
                            Column::make('name')
                                ->heading('Name'),
                            Column::make('serial_device')
                                ->heading('Serial Device'),
                            Column::make('ip_device')
                                ->heading('IP Device'),
                            Column::make('port_device')
                                ->heading('Port Device'),
                            Column::make('status_device')
                                ->heading('Status'),
                            Column::make('created_at')
                                ->heading('Created At')
                        ])
                        ->fromTable()
                        ->only([
                            'name',
                            'serial_device',
                            'ip_device',
                            'port_device',
                            'status_device',
                            'created_at'
                        ])
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV),
                ]),

        ];
    }

    private function getErrorMessage($response): string
    {
        $body = json_decode($response->body(), true);
        if (isset($body['error'])) {
            return $body['error'];
        }
        return $body['message'] ?? $response->body() ?? 'Unknown error occurred';
    }

    private function handleConnectionError(RequestException $e): void
    {
        $errorMessage = 'Connection failed. Please check your internet connection and try again.';
        if ($e->getCode() == 28) { // CURLE_OPERATION_TIMEDOUT
            $errorMessage = 'The request timed out. The server might be down or unreachable.';
        }
        $this->showErrorNotification('Connection Error', $errorMessage);
    }

    private function handleGeneralError(Exception $e): void
    {
        $this->showErrorNotification('Error', $e->getMessage());
    }

    private function showErrorNotification(string $title, string $message): void
    {
        Notification::make()
            ->title($title)
            ->body($message)
            ->danger()
            ->persistent()
            ->send();
    }
}
