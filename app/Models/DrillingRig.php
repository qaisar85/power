<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillingRig extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'type', 'capacity', 'year', 'serial', 'region', 'photos', 'passports', 'status', 'description',
    ];

    protected $casts = [
        'photos' => 'array',
        'passports' => 'array',
        'year' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(DrillingCompany::class, 'company_id');
    }
}