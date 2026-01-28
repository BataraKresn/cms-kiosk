<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ScreenModeEnum: string implements HasLabel, HasColor
{
    case PORTRAIT = 'portrait';
    case LANDSCAPE = 'landscape';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PORTRAIT => 'Portrait',
            self::LANDSCAPE => 'Landscape',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PORTRAIT => 'success',
            self::LANDSCAPE => 'warning',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (ScreenModeEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }
}
