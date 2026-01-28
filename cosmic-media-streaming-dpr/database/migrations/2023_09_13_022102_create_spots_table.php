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
        Schema::create('spots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('layout_id');
            $table->foreignId('media_id')->constrained('media')->nullableOnDelete();
            $table->integer('x')->default(0); // left start
            $table->integer('y')->default(0); // row position
            $table->integer('w')->default(0); // width
            $table->integer('h')->default(0); // height
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
};
