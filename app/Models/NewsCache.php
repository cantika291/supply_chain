<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $fillable = [
        'country_id',
        'title',
        'description',
        'source_name',
        'source_url',
        'category',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sentiment(): HasOne
    {
        return $this->hasOne(NewsSentiment::class);
    }
}