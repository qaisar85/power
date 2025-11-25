<?php

namespace App\Notifications;

use App\Models\ServiceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ServiceDocument $document, public int $days)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Document expiring in {$this->days} days")
            ->greeting('Hello')
            ->line("Your document '{$this->document->type}' is expiring in {$this->days} days.")
            ->line('Expiration date: '.($this->document->expires_at?->toDateString() ?? 'N/A'))
            ->action('View documents', url('/service-dashboard/documents'))
            ->line('Please update or renew the document to keep your account compliant.');
    }
}