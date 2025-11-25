<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'title', 'type', 'file', 'issued_at', 'expires_at', 'verified', 'region', 'description',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(DrillingCompany::class, 'company_id');
    }
}