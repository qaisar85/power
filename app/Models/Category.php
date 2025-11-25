<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'priority', 'color', 'sort_order', 'is_active'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePriority1($query)
    {
        return $query->where('priority', 1);
    }

    public function scopePriority2($query)
    {
        return $query->where('priority', 2);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}