<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeviceStatusEnum: int implements HasLabel, HasColor
{
    case CONNECTED = 0;
    case DISCONNECTED = 1;


    public function getLabel(): ?string
    {
        return match ($this) {
            self::CONNECTED => 'Device Connected - (' . self::CONNECTED->value . ')',
            self::DISCONNECTED => 'Device Disconnected - (' . self::DISCONNECTED->value . ')',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::CONNECTED => 'success',
            self::DISCONNECTED => 'danger',
        };
    }
}
