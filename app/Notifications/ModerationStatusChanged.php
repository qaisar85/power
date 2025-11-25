<?php

namespace App\Notifications;

use App\Models\ModerationTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModerationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public ModerationTask $task)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'moderation.status',
            'task_id' => $this->task->id,
            'item_type' => $this->task->item_type,
            'item_id' => $this->task->item_id,
            'status' => $this->task->status,
            'comment' => $this->task->comment,
            'moderator_id' => $this->task->moderator_id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}