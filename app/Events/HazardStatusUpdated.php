<?php

namespace App\Events;

use App\Models\Hazard;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class HazardStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Hazard $hazard) {}

    public function broadcastOn()
    {
        return new Channel('hazard-channel');
    }

    public function broadcastWith()
    {
        return ['title' => $this->hazard->title, 'status' => $this->hazard->status];
    }
}
