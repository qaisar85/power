<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreelanceSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'category_slug', 'description', 'sort_order', 'is_active'
    ];
}

