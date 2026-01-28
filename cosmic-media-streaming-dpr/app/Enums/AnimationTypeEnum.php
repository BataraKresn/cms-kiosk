<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AnimationTypeEnum: string implements HasLabel, HasColor
{
    case SLIDE = 'slide';
    case FADE = 'fade';
    case FLIP = 'flip';
    case CUBE = 'cube';
    case COVERFLOW = 'coverflow';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SLIDE => 'Slide',
            self::FADE => 'Fade',
            self::FLIP => 'Flip',
            self::CUBE => 'Cube',
            self::COVERFLOW => 'Coverflow',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::SLIDE => 'info',
            self::FADE => 'info',
            self::FLIP => 'info',
            self::CUBE => 'info',
            self::COVERFLOW => 'info',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (AnimationTypeEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }

    public static function getValue($constant)
    {
        return isset(self::$values[$constant]) ? self::$values[$constant] : null;
    }
}
