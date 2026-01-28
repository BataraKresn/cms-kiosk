<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaTypeEnum: string implements HasLabel, HasColor
{
    case IMAGE = 'App\Models\MediaImage';
    case VIDEO = 'App\Models\MediaVideo';
    case HTML = 'App\Models\MediaHtml';
    case LIVE_URL = 'App\Models\MediaLiveUrl';
    case QR_CODE = 'App\Models\MediaQrCode';
    case HLS = 'App\Models\MediaHls';
    case SLIDER = 'App\Models\MediaSlider';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
            self::HTML => 'HTML',
            self::LIVE_URL => 'Live URL',
            self::QR_CODE => 'QRCODE',
            self::HLS => 'HLS',
            self::SLIDER => 'Slider',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::IMAGE => 'info',
            self::VIDEO => 'info',
            self::HTML => 'info',
            self::LIVE_URL => 'info',
            self::QR_CODE => 'warning',
            self::HLS => 'success',
            self::SLIDER => 'info',
        };
    }

    public static function getAsOptions(): array
    {
        $options = [];
        foreach (MediaTypeEnum::cases() as $row) {
            $options[$row->value] = $row->getLabel();
        }

        return $options;
    }

    public static function getValue($constant)
    {
        return isset(self::$values[$constant]) ? self::$values[$constant] : null;
    }
}
