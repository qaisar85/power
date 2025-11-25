<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelanceService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'category',
        'subcategories',
        'price_type',
        'price_value',
        'currency',
        'delivery_days',
        'status',
        'tags',
        'photos',
        'packages',
    ];

    protected $casts = [
        'subcategories' => 'array',
        'tags' => 'array',
        'photos' => 'array',
        'packages' => 'array',
        'price_value' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}