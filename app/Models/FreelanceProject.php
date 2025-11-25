<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelanceProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'category',
        'budget_type',
        'budget_min',
        'budget_max',
        'currency',
        'location',
        'deadline_at',
        'status',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'deadline_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}