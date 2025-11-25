<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'name', 'slug', 'path', 'description', 'icon', 
        'integration_type', 'config', 'is_active', 'requires_auth', 'sort_order'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'requires_auth' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_modules')
            ->withPivot(['permissions', 'last_accessed_at'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}