<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegionalAgent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'region_type',
        'country_id',
        'state_id',
        'city_id',
        'latitude',
        'longitude',
        'commission_rate',
        'service_fee',
        'service_types',
        'supported_categories',
        'is_verified',
        'is_active',
        'verified_at',
        'verified_by',
        'performance_rating',
        'total_services_completed',
        'total_clients_served',
        'total_revenue_generated',
        'business_name',
        'business_description',
        'business_license',
        'certifications',
        'languages',
        'working_hours',
        'timezone',
        'logo',
        'video_resume_url',
        'office_address',
        'office_phone',
        'office_email',
        'office_hours',
    ];

    protected $casts = [
        'service_types' => 'array',
        'supported_categories' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'working_hours' => 'array',
        'office_hours' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'commission_rate' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'performance_rating' => 'decimal:2',
        'total_revenue_generated' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function services(): HasMany
    {
        return $this->hasMany(AgentService::class, 'agent_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AgentReview::class, 'agent_id');
    }

    public function revenueShares(): HasMany
    {
        return $this->hasMany(RevenueShare::class, 'agent_id');
    }

    public function salesTransactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class, 'agent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeInCountry($query, $countryId)
    {
        return $query->where(function ($q) use ($countryId) {
            $q->where('country_id', $countryId)
              ->orWhere('region_type', 'global');
        });
    }

    public function scopeInState($query, $stateId)
    {
        return $query->where(function ($q) use ($stateId) {
            $q->where('state_id', $stateId)
              ->orWhere('region_type', 'global');
        });
    }

    public function scopeInCity($query, $cityId)
    {
        return $query->where(function ($q) use ($cityId) {
            $q->where('city_id', $cityId)
              ->orWhere('region_type', 'global');
        });
    }

    public function scopeOffersService($query, $serviceType)
    {
        return $query->whereJsonContains('service_types', $serviceType);
    }

    public function scopeSupportsCategory($query, $category)
    {
        return $query->whereJsonContains('supported_categories', $category);
    }

    public function scopeTopRated($query, $minRating = 4.0)
    {
        return $query->where('performance_rating', '>=', $minRating);
    }

    // Helper methods
    public function updatePerformanceRating(): void
    {
        $avgRating = $this->reviews()
            ->where('is_visible', true)
            ->avg('rating');

        $this->update([
            'performance_rating' => $avgRating ?? 0,
        ]);
    }

    public function incrementServicesCompleted(): void
    {
        $this->increment('total_services_completed');
    }

    public function incrementClientsServed(): void
    {
        $this->increment('total_clients_served');
    }

    public function addRevenue(float $amount): void
    {
        $this->increment('total_revenue_generated', $amount);
    }

    public function canServeRegion(string $countryId = null, string $stateId = null, string $cityId = null): bool
    {
        if ($this->region_type === 'global') {
            return true;
        }

        if ($cityId && $this->city_id == $cityId) {
            return true;
        }

        if ($stateId && $this->state_id == $stateId) {
            return true;
        }

        if ($countryId && $this->country_id == $countryId) {
            return true;
        }

        return false;
    }

    public function getRegionCoverageAttribute(): string
    {
        switch ($this->region_type) {
            case 'global':
                return 'Global Coverage';
            case 'country':
                return $this->country ? $this->country->name : 'Country';
            case 'state':
                return $this->state ? "{$this->state->name}, {$this->country->name}" : 'State';
            case 'city':
                return $this->city ? "{$this->city->name}, {$this->state->name}" : 'City';
            default:
                return 'Unknown';
        }
    }

    public function getAverageResponseTimeAttribute(): ?float
    {
        // Calculate average response time from services
        return $this->services()
            ->whereNotNull('accepted_at')
            ->get()
            ->map(function ($service) {
                return $service->created_at->diffInHours($service->accepted_at);
            })
            ->avg();
    }

    public function getCompletionRateAttribute(): float
    {
        $total = $this->services()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->services()->where('status', 'completed')->count();
        return ($completed / $total) * 100;
    }
}
