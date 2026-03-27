<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GoldPriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $goldPrice;
    public $usdRate;
    public $snapshot;

    public function __construct($goldPrice, $usdRate, $snapshot)
    {
        $this->goldPrice = $goldPrice;
        $this->usdRate = $usdRate;
        $this->snapshot = $snapshot;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('gold-markets'),
        ];
    }
}
