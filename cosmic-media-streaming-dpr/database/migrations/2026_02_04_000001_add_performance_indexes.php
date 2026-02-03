<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing indexes for query optimization and performance
     * 
     * Focus areas:
     * - Media queries (mediable_type + mediable_id common queries)
     * - Display -> token lookups
     * - Schedule -> playlist relationships
     * - Spot -> media relationships
     */
    public function up(): void
    {
        // Index for faster media lookups by type and ID
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                if (!Schema::hasColumn('media', 'mediable_type_id_idx')) {
                    $table->index(['mediable_type', 'mediable_id'], 'idx_media_mediable');
                }
            });
        }

        // Index for display token lookup (commonly used in queries)
        if (Schema::hasTable('displays')) {
            Schema::table('displays', function (Blueprint $table) {
                if (!Schema::hasColumn('displays', 'idx_display_token')) {
                    $table->index('token', 'idx_display_token');
                }
                if (!Schema::hasColumn('displays', 'idx_display_schedule')) {
                    $table->index('schedule_id', 'idx_display_schedule');
                }
            });
        }

        // Index for spot media lookup (used in layout rendering)
        if (Schema::hasTable('spots')) {
            Schema::table('spots', function (Blueprint $table) {
                if (!Schema::hasColumn('spots', 'idx_spot_media')) {
                    $table->index('media_id', 'idx_spot_media');
                }
                if (!Schema::hasColumn('spots', 'idx_spot_layout')) {
                    $table->index('layout_id', 'idx_spot_layout');
                }
            });
        }

        // Index for schedule playlists lookup
        if (Schema::hasTable('schedule_playlists')) {
            Schema::table('schedule_playlists', function (Blueprint $table) {
                if (!Schema::hasColumn('schedule_playlists', 'idx_schedule_playlists')) {
                    $table->index('schedule_id', 'idx_schedule_playlists_schedule');
                }
                if (!Schema::hasColumn('schedule_playlists', 'idx_schedule_playlists_playlist')) {
                    $table->index('playlist_id', 'idx_schedule_playlists_playlist');
                }
            });
        }

        // Index for playlist layouts lookup
        if (Schema::hasTable('playlist_layouts')) {
            Schema::table('playlist_layouts', function (Blueprint $table) {
                if (!Schema::hasColumn('playlist_layouts', 'idx_playlist_layouts_playlist')) {
                    $table->index('playlist_id', 'idx_playlist_layouts_playlist');
                }
                if (!Schema::hasColumn('playlist_layouts', 'idx_playlist_layouts_layout')) {
                    $table->index('layout_id', 'idx_playlist_layouts_layout');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('idx_media_mediable');
        });

        Schema::table('displays', function (Blueprint $table) {
            $table->dropIndex('idx_display_token');
            $table->dropIndex('idx_display_schedule');
        });

        Schema::table('spots', function (Blueprint $table) {
            $table->dropIndex('idx_spot_media');
            $table->dropIndex('idx_spot_layout');
        });

        Schema::table('schedule_playlists', function (Blueprint $table) {
            $table->dropIndex('idx_schedule_playlists_schedule');
            $table->dropIndex('idx_schedule_playlists_playlist');
        });

        Schema::table('playlist_layouts', function (Blueprint $table) {
            $table->dropIndex('idx_playlist_layouts_playlist');
            $table->dropIndex('idx_playlist_layouts_layout');
        });
    }
};
