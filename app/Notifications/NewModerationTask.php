<?php

namespace App\Notifications;

use App\Models\ModerationTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewModerationTask extends Notification
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
            'type' => 'moderation.new',
            'task_id' => $this->task->id,
            'item_type' => $this->task->item_type,
            'item_id' => $this->task->item_id,
            'priority' => (int) $this->task->priority,
            'submitted_by' => $this->task->submitted_by,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}