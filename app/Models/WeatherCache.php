<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherCache extends Model
{
    protected $table = 'weather_cache';

    protected $fillable = [
        'country_id',
        'temperature',
        'rainfall',
        'wind_speed',
        'storm_risk',
        'fetched_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'rainfall' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'fetched_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}