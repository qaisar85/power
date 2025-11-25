<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrillingCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'rig_id',
        'title',
        'client',
        'region',
        'method',
        'depth',
        'start_date',
        'end_date',
        'status',
        'summary',
        'tags',
        'photos',
        'documents',
        'metrics',
        'verified'
    ];

    protected $casts = [
        'photos' => 'array',
        'documents' => 'array',
        'metrics' => 'array',
        'tags' => 'array',
        'verified' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'depth' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(DrillingCompany::class, 'company_id');
    }

    public function rig()
    {
        return $this->belongsTo(DrillingRig::class, 'rig_id');
    }
}