<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->timestamp('last_seen')->nullable()->after('status')->comment('Last time device responded successfully');
            $table->timestamp('last_checked_at')->nullable()->after('last_seen')->comment('Last time system checked device status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->dropColumn(['last_seen', 'last_checked_at']);
        });
    }
};
