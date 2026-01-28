<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaSliderContent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'path',
        'duration',
        'mime',
    ];
}
