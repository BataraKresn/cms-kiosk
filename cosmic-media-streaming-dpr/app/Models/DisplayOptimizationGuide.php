<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Display Model dengan performance optimizations
 * 
 * Best practices:
 * - Always use eager loading (with()) untuk prevent N+1 queries
 * - Use local scopes para sa common query patterns
 * - Cache results when appropriate
 */
class DisplayOptimized
{
    /**
     * Scope: Get display with all needed relationships untuk content rendering
     * Use: Display::withContent()->where('token', $token)->first()
     */
    public function scopeWithContent(Builder $query): Builder
    {
        return $query->with([
            'schedule:id,name',
            'schedule.schedule_playlists:schedule_id,start_day,end_day,playlist_id',
            'schedule.schedule_playlists.playlists:id',
            'schedule.schedule_playlists.playlists.playlist_layouts:id,playlist_id,layout_id,start_time,end_time',
            'schedule.schedule_playlists.playlists.playlist_layouts.layout:id,name,screen_id',
            'schedule.schedule_playlists.playlists.playlist_layouts.layout.screen:id,mode,width,height,column,row',
            'schedule.schedule_playlists.playlists.playlist_layouts.layout.spots:layout_id,id,media_id,x,y,w,h',
            'screen:id,mode,width,height,column,row',
        ]);
    }

    /**
     * Scope: Get minimal display info (untuk listing/search)
     * Use: Display::minimal()->get()
     */
    public function scopeMinimal(Builder $query): Builder
    {
        return $query->select('id', 'name', 'token', 'schedule_id', 'status', 'last_seen_at');
    }

    /**
     * Scope: Get active displays only
     * Use: Display::active()->get()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Connected')
            ->whereNull('deleted_at');
    }

    /**
     * Scope: Get displays updated after timestamp (untuk incremental syncs)
     * Use: Display::updatedAfter($timestamp)->get()
     */
    public function scopeUpdatedAfter(Builder $query, $timestamp): Builder
    {
        return $query->where('updated_at', '>=', $timestamp);
    }
}

/**
 * Usage Examples:
 * 
 * ❌ BAD - Causes N+1 queries:
 * $displays = Display::all();
 * foreach ($displays as $display) {
 *     echo $display->schedule->name;
 * }
 * 
 * ✅ GOOD - Single query with eager loading:
 * $displays = Display::with('schedule')->get();
 * foreach ($displays as $display) {
 *     echo $display->schedule->name;
 * }
 * 
 * ✅ BETTER - Use scopes:
 * $displays = Display::minimal()->active()->get();
 * 
 * ✅ BEST - Cache hasil:
 * $displays = Cache::tags(['display'])->remember(
 *     'active_displays',
 *     3600,
 *     fn() => Display::withContent()->active()->get()
 * );
 */
