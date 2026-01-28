<?php

namespace Database\Seeders;

use App\Models\Layout;
use App\Models\Spot;
use Illuminate\Database\Seeder;

class LayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Layout::query()->truncate();
        Spot::query()->truncate();

        $layout = Layout::create([
            'name' => 'Template (7 Spot)',
            'screen_id' => 1,
            'running_text_is_include' => false,
            'running_text_position' => 'bottom',
            'is_template' => true,
        ]);

        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 0,
            'y' => 0,
            'w' => 18,
            'h' => 2,
        ]);
        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 0,
            'y' => 2,
            'w' => 18,
            'h' => 9,
        ]);
        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 0,
            'y' => 12,
            'w' => 9,
            'h' => 2,
        ]);
        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 10,
            'y' => 12,
            'w' => 9,
            'h' => 2,
        ]);
        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 0,
            'y' => 15,
            'w' => 9,
            'h' => 18,
        ]);
        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 9,
            'y' => 14,
            'w' => 9,
            'h' => 9,
        ]);
        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 9,
            'y' => 20,
            'w' => 9,
            'h' => 9,
        ]);

        $layout = Layout::create([
            'name' => 'Template (1 Spot Full)',
            'screen_id' => 1,
            'running_text_is_include' => false,
            'running_text_position' => 'bottom',
            'is_template' => true,
        ]);

        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 0,
            'y' => 0,
            'w' => 18,
            'h' => 32,
        ]);

        $layout = Layout::create([
            'name' => 'Template (1 Spot Full Landscape)',
            'screen_id' => 2,
            'running_text_is_include' => false,
            'running_text_position' => 'bottom',
            'is_template' => true,
        ]);

        Spot::create([
            'layout_id' => $layout->id,
            'media_id' => 1,
            'x' => 0,
            'y' => 0,
            'w' => 32,
            'h' => 18,
        ]);
    }
}
