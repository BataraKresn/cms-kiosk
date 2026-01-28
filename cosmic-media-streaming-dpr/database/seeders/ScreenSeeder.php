<?php

namespace Database\Seeders;

use App\Models\Screen;
use Illuminate\Database\Seeder;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Screen::query()->truncate();

        Screen::create([
            'name' => '1080p Portrait (9:16)',
            'width' => 1080,
            'height' => 1920,
            'mode' => 'portrait',
            'aspect_ratio' => '9:16',
            'column' => 18,
            'row' => 32,
        ]);

        Screen::create([
            'name' => '1080p (16:9)',
            'width' => 1920,
            'height' => 1080,
            'mode' => 'landscape',
            'aspect_ratio' => '16:9',
            'column' => 32,
            'row' => 18,
        ]);

        Screen::create([
            'name' => '1024x608 (16:9.5)',
            'width' => 1024,
            'height' => 608,
            'mode' => 'landscape',
            'aspect_ratio' => '16:9.5',
            'column' => 32,
            'row' => 19,
        ]);

        Screen::create([
            'name' => '900x600 (3:2)',
            'width' => 900,
            'height' => 600,
            'mode' => 'landscape',
            'aspect_ratio' => '3:2',
            'column' => 6,
            'row' => 4,
        ]);
    }
}
