<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Tobuli\Entities\Task;

class NewTask extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $task;

    public function __construct(Task $task) {
        $this->task = $task;
    }

    public function broadcastOn() {
        return [md5('task_for_'. 1)];
    }

    public function broadcastAs()
    {
        return 'task';
    }
}
