<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix media_htmls paths - add 'html/' prefix if not exists
        DB::table('media_htmls')
            ->whereNotNull('path')
            ->where('path', 'not like', 'html/%')
            ->update([
                'path' => DB::raw("CONCAT('html/', path)")
            ]);

        // Fix media_images paths - add 'image/' prefix if not exists
        DB::table('media_images')
            ->whereNotNull('path')
            ->where('path', 'not like', 'image/%')
            ->update([
                'path' => DB::raw("CONCAT('image/', path)")
            ]);

        $this->command->info('Media storage paths have been fixed.');
        $this->command->info('HTML files: Added html/ prefix where missing');
        $this->command->info('Image files: Added image/ prefix where missing');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'html/' prefix from media_htmls
        DB::table('media_htmls')
            ->whereNotNull('path')
            ->where('path', 'like', 'html/%')
            ->update([
                'path' => DB::raw("REPLACE(path, 'html/', '')")
            ]);

        // Remove 'image/' prefix from media_images
        DB::table('media_images')
            ->whereNotNull('path')
            ->where('path', 'like', 'image/%')
            ->update([
                'path' => DB::raw("REPLACE(path, 'image/', '')")
            ]);

        $this->command->info('Media storage paths have been reverted.');
    }
};
