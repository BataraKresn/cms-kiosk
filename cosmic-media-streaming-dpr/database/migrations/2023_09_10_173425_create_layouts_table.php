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
        Schema::create('layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('screen_id');
            $table->boolean('running_text_is_include')->default(false);
            $table->string('running_text_position')->default('bottom');
            $table->unsignedBigInteger('running_text_id')->nullable();
            $table->boolean('is_template')->default(false);
            $table->text('children')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layouts');
    }
};
