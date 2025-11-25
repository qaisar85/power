<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActionLog extends Model
{
    protected $table = 'admin_actions_log';

    protected $fillable = [
        'admin_id',
        'action_type',
        'target_id',
        'target_type',
        'comment',
        'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}