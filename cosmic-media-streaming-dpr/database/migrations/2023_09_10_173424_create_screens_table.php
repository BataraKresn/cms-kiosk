<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('width'); // in pixel
            $table->integer('height'); // in pixel
            $table->string('mode'); // square / portrait / landscape
            $table->string('aspect_ratio'); // (square) 1:1, (portrait) 9:16,4:3,1:1.9 (4K/2K), 2:3 (landscape) 16:9,3:4,1.9:1 (4K/2K), 3:2
            $table->integer('column')->default(0); // 32/18
            $table->integer('row')->default(0); // 18/32
            // TODO: I think we need integer value for this later.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
