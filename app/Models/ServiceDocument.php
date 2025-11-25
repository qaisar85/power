<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDocument extends Model
{
    protected $fillable = [
        'company_id','type','filename','expires_at','status','is_rejected'
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_rejected' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}