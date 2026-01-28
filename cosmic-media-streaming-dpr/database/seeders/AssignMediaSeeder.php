<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\MediaImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AssignMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Schema::disableForeignKeyConstraints();

        MediaImage::query()->truncate();
        Media::query()->truncate();

        $mediable = new MediaImage([
            'name' => 'No Media',
            'slug' => 'no-media-content',
            'path' => 'no-media.png',
        ]);
        $mediable->save();

        $media = new Media([
            'name' => 'No Media',
            'description' => 'No Media',
        ]);

        $mediable->media()->save($media);

        Schema::enableForeignKeyConstraints();
    }
}
