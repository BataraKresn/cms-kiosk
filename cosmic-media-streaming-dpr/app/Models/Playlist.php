<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Playlist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_all_day',
        'layout_interval',
    ];

    public function playlist_layouts()
    {
        return $this->hasMany(PlaylistLayout::class, 'playlist_id', 'id');
    }
}
