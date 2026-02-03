<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Display extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'token',
        'name',
        'description',
        'screen_id',
        'display_type',
        'operating_system',
        'schedule_id',
        'lat',
        'lng',
        'location_description',
        'group',
        'location',
    ];

    protected $appends = [
        'location',
    ];

    /**
     * Scope: Get display with all needed relationships untuk content rendering
     * Prevents N+1 queries dengan eager loading complete hierarchy
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
     * Scope: Get minimal display info untuk listing/search operations
     * Reduces data transfer size
     */
    public function scopeMinimal(Builder $query): Builder
    {
        return $query->select('id', 'name', 'token', 'schedule_id', 'status', 'last_seen_at');
    }

    /**
     * Scope: Get active displays only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Connected')->whereNull('deleted_at');
    }

    public function location(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => json_encode([
                'lat' => isset($attributes['lat']) ? (float) $attributes['lat'] : -6.210622064407504,
                'lng' => isset($attributes['lng']) ? (float) $attributes['lng'] : 106.80258222096496,
            ]),
            set: fn($value) => [
                'lat' => $value['lat'],
                'lng' => $value['lng'],
            ],
        );
    }

    public function newToken()
    {
        $datetime = date('Ymdhis');

        $this->token = Str::random(10) . $datetime;
        $this->name = 'New Display ' . $datetime;
        $this->save();
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class, 'screen_id', 'id');
    }
}
