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
            // Index for soft deletes query (WHERE deleted_at IS NULL)
            $table->index('deleted_at');
            
            // Index for sorting by created_at
            $table->index('created_at');
            
            // Index for searchable name column
            $table->index('name');
            
            // Composite index for common query pattern: deleted_at + created_at
            $table->index(['deleted_at', 'created_at'], 'idx_deleted_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remotes', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['name']);
            $table->dropIndex('idx_deleted_created');
        });
    }
};
