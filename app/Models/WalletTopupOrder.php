<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTopupOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_order_id',
        'provider_session_id',
        'status',
        'amount_native',
        'currency_native',
        'amount_usd',
        'capture_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount_native' => 'float',
        'amount_usd' => 'float',
    ];
}