<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'user_id',
        'agent_id',
        'share_type',
        'percentage',
        'amount_earned',
        'platform_fee',
        'agent_commission',
        'status',
        'payment_date',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount_earned' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'agent_commission' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(RegionalAgent::class, 'agent_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
