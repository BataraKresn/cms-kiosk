<?php

namespace App\Models;

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
