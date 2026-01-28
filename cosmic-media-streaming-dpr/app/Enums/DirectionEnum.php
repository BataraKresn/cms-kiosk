<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DirectionEnum: string implements HasLabel, HasColor
{
    case LEFT = 'left';
    case RIGHT = 'right';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LEFT => 'Left',
            self::RIGHT => 'Right',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::LEFT => 'success',
            self::RIGHT => 'info',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (DirectionEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }
}
