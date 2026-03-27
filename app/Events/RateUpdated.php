<?php

namespace App\Events;

use App\Models\ExchangeRate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Important!
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RateUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rate;

    public function __construct(ExchangeRate $rate)
    {
        $this->rate = $rate;
    }

    public function broadcastOn(): array
    {
        // This matches the channel name in your History.vue
        return [
            new Channel('rates-updates'),
        ];
    }
}
