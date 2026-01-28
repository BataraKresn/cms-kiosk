<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MimeEnum: string implements HasLabel, HasColor
{
    case JPG = 'image/jpg';
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';
    case HTML = 'text/html';
    case MP4 = 'video/mp4';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::JPG => self::JPG->value,
            self::JPEG => self::JPEG->value,
            self::PNG => self::PNG->value,
            self::HTML => self::HTML->value,
            self::MP4 => self::MP4->value,
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::JPG => 'info',
            self::JPEG => 'info',
            self::PNG => 'info',
            self::HTML => 'info',
            self::MP4 => 'info',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (MimeEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }
}
