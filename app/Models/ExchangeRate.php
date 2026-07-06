<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = [
        'currency_code',
        'rate_to_usd',
        'rate_date',
    ];

    protected $casts = [
        'rate_to_usd' => 'decimal:6',
        'rate_date' => 'date',
    ];
}