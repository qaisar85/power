<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'listing_id',
        'buyer_id',
        'seller_id',
        'agent_id',
        'total_amount',
        'currency',
        'platform_fee',
        'agent_commission',
        'seller_amount',
        'payment_method',
        'payment_provider',
        'payment_reference',
        'payment_status',
        'platform_fee_collected',
        'agent_paid',
        'seller_paid',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'agent_commission' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'platform_fee_collected' => 'boolean',
        'agent_paid' => 'boolean',
        'seller_paid' => 'boolean',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = 'TXN-' . strtoupper(Str::random(12));
            }
        });
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(RegionalAgent::class, 'agent_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Calculate and set the transaction splits
     */
    public function calculateSplits(float $platformFeePercentage = 5.0): void
    {
        $this->platform_fee = ($this->total_amount * $platformFeePercentage) / 100;

        if ($this->agent_id && $this->agent) {
            $this->agent_commission = ($this->total_amount * $this->agent->commission_rate) / 100;
        } else {
            $this->agent_commission = 0;
        }

        $this->seller_amount = $this->total_amount - $this->platform_fee - $this->agent_commission;
    }
}
