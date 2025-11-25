<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'company_id','title','description','category','subcategory','service_type','price_type','price_value','currency','price_details','geo','files','placement_type','visibility'
    ];

    protected $casts = [
        'geo' => 'array',
        'files' => 'array',
        'price_value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(ServiceCase::class);
    }
}