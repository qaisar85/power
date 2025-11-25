<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'user_id',
        'agent_service_id',
        'rating',
        'comment',
        'communication_rating',
        'professionalism_rating',
        'response_time_rating',
        'quality_rating',
        'is_verified',
        'is_visible',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(RegionalAgent::class, 'agent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(AgentService::class, 'agent_service_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
