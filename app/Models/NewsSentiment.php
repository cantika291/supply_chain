<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsSentiment extends Model
{
    protected $table = 'news_sentiments';

    protected $fillable = [
        'news_cache_id',
        'positive_score',
        'negative_score',
        'sentiment',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(NewsCache::class, 'news_cache_id');
    }
}