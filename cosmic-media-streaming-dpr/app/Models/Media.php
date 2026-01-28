<?php

namespace App\Models;

use App\Enums\MediaTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory;
    use SoftDeletes;

    const NO_MEDIA = 1;

    protected $fillable = [
        'name',
        'description',
        'mediable_type',
        'mediable_id',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getNameTypeAttribute()
    {
        $type = MediaTypeEnum::getAsOptions()[$this->mediable_type];

        return sprintf('%s (%s)', $this->name, $type);
    }
}
