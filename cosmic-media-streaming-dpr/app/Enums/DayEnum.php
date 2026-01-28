<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DayEnum: int implements HasLabel, HasColor
{
    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUNDAY => 'Sunday - (' . self::SUNDAY->value . ')',
            self::MONDAY => 'Monday - (' . self::MONDAY->value . ')',
            self::TUESDAY => 'Tuesday - (' . self::TUESDAY->value . ')',
            self::WEDNESDAY => 'Wednesday - (' . self::WEDNESDAY->value . ')',
            self::THURSDAY => 'Thursday - (' . self::THURSDAY->value . ')',
            self::FRIDAY => 'Friday - (' . self::FRIDAY->value . ')',
            self::SATURDAY => 'Saturday - (' . self::SATURDAY->value . ')',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::SUNDAY => 'danger',
            self::MONDAY => 'info',
            self::TUESDAY => 'info',
            self::WEDNESDAY => 'info',
            self::THURSDAY => 'info',
            self::FRIDAY => 'info',
            self::SATURDAY => 'warning',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (DayEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }
}
