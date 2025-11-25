<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Admin;
use App\Notifications\NewModerationTask;

class ModerationTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type',
        'item_id',
        'submitted_by',
        'status',
        'moderator_id',
        'comment',
        'priority',
        'country',
        'region',
        'category_code',
        'subsector_id',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function moderator()
    {
        return $this->belongsTo(Admin::class, 'moderator_id');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    protected static function booted(): void
    {
        static::creating(function (ModerationTask $task) {
            // Simple auto-assessment hook: set default priority by item_type if none provided
            if ($task->priority === null) {
                $task->priority = match ($task->item_type) {
                    'complaint', 'spam', 'inspection_request' => 2,
                    'auction' => 1,
                    default => 0,
                };
            }
        });

        static::created(function (ModerationTask $task) {
            // Notify admins with moderation permission
            $admins = Admin::all()->filter(fn ($a) => $a->can('moderation'));
            foreach ($admins as $admin) {
                $admin->notify(new NewModerationTask($task));
            }
        });
    }
}