<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Port extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'port_code',
        'latitude',
        'longitude',
        'harbor_type',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}