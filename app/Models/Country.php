<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'official_name',
        'cca3',
        'cca2',
        'region',
        'subregion',
        'capital',
        'currency_code',
        'currency_name',
        'language',
        'latitude',
        'longitude',
        'flag_url',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function economicData(): HasMany
    {
        return $this->hasMany(CountryEconomicData::class);
    }

    public function latestEconomicData(): HasOne
    {
        return $this->hasOne(CountryEconomicData::class)->latestOfMany('year');
    }

    public function weatherCache(): HasOne
    {
        return $this->hasOne(WeatherCache::class)->latestOfMany();
    }

    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }

    public function newsCache(): HasMany
    {
        return $this->hasMany(NewsCache::class);
    }

    public function riskScore(): HasOne
    {
        return $this->hasOne(RiskScore::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function watchedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'watchlists');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
    return $this->hasMany(Favorite::class);
    }
}