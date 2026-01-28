<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulePlaylist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'playlist_id',
        'start_day',
        'end_day',
    ];

    public function playlists()
    {
        return $this->hasMany(Playlist::class, 'id', 'playlist_id');
    }
}
