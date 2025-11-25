<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'supplier_company_id',
        'user_id',
        'price',
        'currency',
        'deadline_days',
        'comment',
        'files',
        'status',
    ];

    protected $casts = [
        'files' => 'array',
        'price' => 'decimal:2',
    ];

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}