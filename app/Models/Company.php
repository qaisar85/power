<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'industry', 'website',
        'phone', 'email', 'address', 'country', 'logo', 'status', 'sector_id',
        'city', 'postal_code', 'registration_number', 'tax_id',
        'founded_year', 'employee_count', 'annual_revenue', 'currency'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function sector()
    {
        return $this->belongsTo(BusinessSector::class, 'sector_id');
    }
}