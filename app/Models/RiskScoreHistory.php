<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScoreHistory extends Model
{
    protected $table = 'risk_score_histories';

    protected $fillable = [
        'risk_score_id',
        'total_score',
        'risk_level',
        'recorded_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function riskScore(): BelongsTo
    {
        return $this->belongsTo(RiskScore::class);
    }
}