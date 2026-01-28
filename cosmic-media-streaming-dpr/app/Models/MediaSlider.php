<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaSlider extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'animation_type',
    ];

    public function media_slider_contents()
    {
        return $this->hasMany(MediaSliderContent::class, 'media_slider_id', 'id');
    }
}
