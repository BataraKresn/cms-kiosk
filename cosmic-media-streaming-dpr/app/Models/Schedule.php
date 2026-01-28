<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_whole_week',
        'running_text_is_include',
        'running_text_position',
        'running_text_id',
    ];

    public function schedule_playlists()
    {
        return $this->hasMany(SchedulePlaylist::class, 'schedule_id', 'id');
    }
}
