<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessSubsector;

class BusinessSector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'code', 'description', 'parent_id', 'level', 'is_active', 'sort_order'
    ];

    public function parent()
    {
        return $this->belongsTo(BusinessSector::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BusinessSector::class, 'parent_id')->orderBy('sort_order');
    }

    public function subsectors()
    {
        return $this->hasMany(BusinessSubsector::class, 'sector_id')->orderBy('sort_order');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'sector_id');
    }

    public function scopeSectors($query)
    {
        return $query->where('level', 1);
    }

    public function scopeSubSectors($query)
    {
        return $query->where('level', 2);
    }

    public function scopeSubSubSectors($query)
    {
        return $query->where('level', 3);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}