<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Layout extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'screen_id',
        'running_text_is_include',
        'running_text_position',
        'running_text_id',
        'is_template',
    ];

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function running_text()
    {
        return $this->belongsTo(RunningText::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }

    public function scopeValid($query)
    {
        return $query->where('is_template', false);
    }
}
