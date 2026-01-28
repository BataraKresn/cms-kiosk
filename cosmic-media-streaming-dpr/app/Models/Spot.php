<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spot extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'layout_id',
        'media_id',
    ];

    public function media()
    {
        return $this->belongsTo('App\Models\Media');
    }
}
