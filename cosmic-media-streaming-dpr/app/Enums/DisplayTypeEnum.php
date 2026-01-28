<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DisplayTypeEnum: string implements HasLabel, HasColor
{
    case DIGITAL_SIGNAGE = 'digital_signage';
    case LED_VIDEOTRON = 'led_videotron';
    case OTHER = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DIGITAL_SIGNAGE => 'Digital Signage',
            self::LED_VIDEOTRON => 'LED / Videotron',
            self::OTHER => 'Other',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::DIGITAL_SIGNAGE => 'success',
            self::LED_VIDEOTRON => 'info',
            self::OTHER => 'primary',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (DisplayTypeEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }
}
