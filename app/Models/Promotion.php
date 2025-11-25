<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type', // percent|amount
        'value', // numeric value; percent in 0-100, amount in currency units
        'currency', // currency for fixed-amount type; null means package currency
        'applies_to_package_id', // null means global
        'max_uses',
        'times_used',
        'valid_from',
        'valid_to',
        'active',
        'meta',
    ];

    protected $casts = [
        'value' => 'float',
        'max_uses' => 'int',
        'times_used' => 'int',
        'active' => 'bool',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'meta' => 'array',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'applies_to_package_id');
    }

    public function redemptions()
    {
        return $this->hasMany(PromotionRedemption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isCurrentlyValid(): bool
    {
        if (!$this->active) return false;
        $now = Carbon::now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_to && $now->gt($this->valid_to)) return false;
        if ($this->max_uses !== null && $this->times_used >= $this->max_uses) return false;
        return true;
    }

    public function appliesToPackage(?int $packageId): bool
    {
        if ($this->applies_to_package_id === null) return true;
        return $packageId === $this->applies_to_package_id;
    }
}