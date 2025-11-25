<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShippingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Listing $listing,
        public User $requester,
        public ?string $message = null,
        public array $meta = []
    ) {}

    public function build()
    {
        return $this->subject('New shipping request for: ' . ($this->listing->title ?? 'Listing #' . $this->listing->id))
            ->view('emails.shipping_request')
            ->with([
                'listing' => $this->listing,
                'requester' => $this->requester,
                'message' => $this->message,
                'meta' => $this->meta,
            ]);
    }
}