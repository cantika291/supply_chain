<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryEconomicData extends Model
{
    protected $table = 'country_economic_data';

    protected $fillable = [
        'country_id',
        'year',
        'gdp',
        'inflation_rate',
        'population',
        'exports_value',
        'imports_value',
    ];

    protected $casts = [
        'gdp' => 'decimal:2',
        'inflation_rate' => 'decimal:4',
        'exports_value' => 'decimal:2',
        'imports_value' => 'decimal:2',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}