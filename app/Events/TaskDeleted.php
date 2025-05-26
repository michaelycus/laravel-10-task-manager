<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $taskId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $taskId) // Pass ID as task model won't exist post-delete
    {
        $this->taskId = $taskId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tasks'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.deleted';
    }

    public function broadcastWith(): array
    {
        return ['taskId' => $this->taskId];
    }
}
