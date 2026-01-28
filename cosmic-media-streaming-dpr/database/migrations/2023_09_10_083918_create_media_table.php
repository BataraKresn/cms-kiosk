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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->morphs('mediable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }

    /*
     * medias
    name
    description
    mediable_type
        image (MediaImage)
        video (MediaVideo)
        html (MediaHtml)
        live_url (MediaLiveUrl)
        qr_code (MediaQrCode)
        hls (MediaHls)
        slider (MediaSlider)
    mediable_id
     */
};
