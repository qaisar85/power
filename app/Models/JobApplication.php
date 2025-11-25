<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id', 'user_id', 'cover_letter', 'resume', 'expected_salary', 'status',
        'currency', 'availability_date', 'notice_period', 'first_name', 'last_name',
        'email', 'phone', 'address', 'city', 'country', 'postal_code',
        'date_of_birth', 'nationality', 'education', 'experience', 'skills',
        'languages', 'certifications'
    ];

    protected $casts = [
        'education' => 'array',
        'experience' => 'array',
        'skills' => 'array',
        'languages' => 'array',
        'certifications' => 'array',
        'availability_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}