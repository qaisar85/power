<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminCategoryAssignment extends Model
{
    protected $fillable = [
        'admin_id',
        'subsector_id',
        'category_code',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function subsector(): BelongsTo
    {
        return $this->belongsTo(BusinessSubsector::class, 'subsector_id');
    }
}