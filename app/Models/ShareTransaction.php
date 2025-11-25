<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // buy or sell
        'shares',
        'price_per_share',
        'amount',
        'status', // pending, confirmed, cancelled
        'payment_method',
        'payment_reference',
        'certificate_path',
    ];
}