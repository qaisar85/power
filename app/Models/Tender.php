<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'category',
        'subcategory',
        'description',
        'location',
        'country',
        'region',
        'city',
        'budget_min',
        'budget_max',
        'currency',
        'deadline_at',
        'visibility',
        'status', // under_review, published, rejected OR pending, published, closed
        'attachments',
        'options',
        'link_token',
    ];

    protected $casts = [
        'attachments' => 'array',
        'options' => 'array',
        'deadline_at' => 'datetime',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(TenderApplication::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['published']) && (!$this->deadline_at || now()->lt($this->deadline_at));
    }
}