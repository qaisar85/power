<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSubsector extends Model
{
    protected $fillable = [
        'sector_id', 'name', 'slug', 'description', 'standard', 'code', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(BusinessSector::class, 'sector_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}