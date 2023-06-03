<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $message;

    public $user;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        $channels[] = $this->message->chat->room_hash;

        foreach ($this->message->chat->participants as $participant) {
            if (! $participant->isUser()) {
                continue;
            }

            if (! $participant->chattable) {
                continue;
            }

            if (! $participant->chattable->perm('chat', 'view')) {
                continue;
            }

            $channels[] = md5('message_for_user_'.$participant->chattable->id);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'message';
    }
}
