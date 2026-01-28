<?php

use App\Enums\DisplayTypeEnum;
use App\Enums\OperatingSystemEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('displays', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('screen_id')->nullable();
            $table->string('display_type')->default(DisplayTypeEnum::OTHER);
            $table->string('operating_system')->default(OperatingSystemEnum::ANDROID);
            $table->foreignId('schedule_id')->nullable();
            $table->decimal('lat', 11, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->text('location')->nullable();
            $table->text('location_description')->nullable();
            $table->string('group')->nullable(); // maybe can be attachable
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displays');
    }
};
