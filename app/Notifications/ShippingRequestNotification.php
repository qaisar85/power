<?php

namespace App\Notifications;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ShippingRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Listing $listing,
        public User $requester,
        public ?string $message = null,
        public array $meta = []
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New shipping request for: ' . ($this->listing->title ?? 'Listing #' . $this->listing->id))
            ->line('You have received a shipping request.')
            ->line('Listing: ' . ($this->listing->title ?? 'Listing #' . $this->listing->id))
            ->line('From: ' . $this->requester->name . ' (' . $this->requester->email . ')')
            ->line($this->message ? ('Message: ' . $this->message) : '')
            ->action('View Listing', route('listings.show', $this->listing->id));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'shipping',
            'listing_id' => $this->listing->id,
            'listing_title' => $this->listing->title,
            'requester_id' => $this->requester->id,
            'requester_name' => $this->requester->name,
            'requester_email' => $this->requester->email,
            'message' => $this->message,
            'meta' => $this->meta,
        ];
    }
}