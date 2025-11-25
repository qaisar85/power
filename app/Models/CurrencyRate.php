<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'usd_rate',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'usd_rate' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];

    public static function rateFor(string $currency): ?float
    {
        $currency = strtoupper(trim($currency));
        if ($currency === 'USD') {
            return 1.0;
        }
        $rate = static::where('currency', $currency)->value('usd_rate');
        return $rate === null ? null : (float) $rate;
    }

    public static function convert(float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $fromCurrency = strtoupper(trim($fromCurrency));
        $toCurrency = strtoupper(trim($toCurrency));

        $fromRate = static::rateFor($fromCurrency);
        $toRate = static::rateFor($toCurrency);

        if ($fromRate === null || $toRate === null) {
            return null;
        }

        // Convert to USD then to target
        $amountUsd = $amount * $fromRate; // 1 from currency unit to USD
        $converted = $amountUsd / $toRate; // USD to target currency
        return $converted;
    }

    public static function toUsd(float $amount, string $currency): ?float
    {
        $rate = static::rateFor($currency);
        if ($rate === null) return null;
        return $amount * $rate;
    }
}