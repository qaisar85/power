<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'user_id',
        'package_id',
        'discount_amount',
        'discount_currency',
        'meta',
    ];

    protected $casts = [
        'discount_amount' => 'float',
        'meta' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}