<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'layout_id',
        'start_time',
        'end_time',
    ];

    public function layout()
    {
        return $this->belongsTo(Layout::class, 'layout_id', 'id');
    }
}
