<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'citizenship',
        'passport_number',
        'country_of_residence',
        'passport_photo_path',
        'selfie_photo_path',
        'status',
        'moderator_id',
        'notes',
    ];
}