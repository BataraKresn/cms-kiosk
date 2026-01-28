<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OperatingSystemEnum: string implements HasLabel, HasColor
{
    case ANDROID = 'android';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ANDROID => 'Android',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ANDROID => 'success',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (OperatingSystemEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }
}
