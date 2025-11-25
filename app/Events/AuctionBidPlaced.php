<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionBidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $listingId;
    public int $userId;
    public float $amount;
    public string $broadcastQueue = 'default';

    public function __construct(int $listingId, int $userId, float $amount)
    {
        $this->listingId = $listingId;
        $this->userId = $userId;
        $this->amount = $amount;
    }

    public function broadcastOn(): Channel
    {
        // Public channel per listing for simplicity. Switch to PrivateChannel if auth needed.
        return new Channel('auction.' . $this->listingId);
    }

    public function broadcastAs(): string
    {
        return 'bid.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'listing_id' => $this->listingId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}