<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'type', // product, service, vacancy, news, tender, auction
        'title',
        'description',
        'photos',
        'documents',
        'price',
        'currency',
        'status', // draft, under_review, published, rejected
        'location',
        'deal_type', // sale, rent, auction
        'payment_options',
        'category',
        'subcategories',
        'publish_in_rent',
        'publish_in_auction',
        'rent_fields',
        'auction_fields',
        'logistics_fields',
        'product_fields',
        'business_fields',
        'preview_comment',
        'package',
    ];

    protected $casts = [
        'photos' => 'array',
        'documents' => 'array',
        'payment_options' => 'array',
        'subcategories' => 'array',
        'publish_in_rent' => 'boolean',
        'publish_in_auction' => 'boolean',
        'rent_fields' => 'array',
        'auction_fields' => 'array',
        'logistics_fields' => 'array',
        'product_fields' => 'array',
        'business_fields' => 'array',
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}