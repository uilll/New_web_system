<?php

namespace App\Listeners;

use App\Events\NewMessage;
use Illuminate\Queue\Listener;

class NewMessageListener extends Listener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(NewMessage $event)
    {
    }
}
