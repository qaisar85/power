<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticsRoute extends Model
{
    protected $fillable = [
        'company_id', 'service_id', 'from_country', 'from_city', 'to_country', 'to_city',
        'transport_type', 'frequency', 'timeline_days', 'price_per_kg', 'price_per_ton', 'price_per_container',
        'documents', 'conditions',
    ];

    protected $casts = [
        'documents' => 'array',
        'timeline_days' => 'integer',
        'price_per_kg' => 'decimal:2',
        'price_per_ton' => 'decimal:2',
        'price_per_container' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}