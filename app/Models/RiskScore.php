<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskScore extends Model
{
    protected $table = 'risk_scores';

    protected $fillable = [
        'country_id',
        'weather_score',
        'inflation_score',
        'currency_score',
        'news_score',
        'total_score',
        'risk_level',
        'calculated_at',
    ];

    protected $casts = [
        'weather_score' => 'decimal:2',
        'inflation_score' => 'decimal:2',
        'currency_score' => 'decimal:2',
        'news_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RiskScoreHistory::class);
    }
}