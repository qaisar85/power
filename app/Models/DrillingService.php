<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'method',
        'depth',
        'region',
        'certificates',
        'description',
        'price',
        'currency',
    ];

    protected $casts = [
        'certificates' => 'array',
        'depth' => 'integer',
        'price' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(DrillingCompany::class, 'company_id');
    }
}