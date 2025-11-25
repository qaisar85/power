<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentService extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'company_id',
        'listing_id',
        'service_type',
        'description',
        'price',
        'currency',
        'status',
        'commission_amount',
        'accepted_at',
        'completed_at',
        'rating',
        'review',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(RegionalAgent::class, 'agent_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'requested');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['accepted', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
