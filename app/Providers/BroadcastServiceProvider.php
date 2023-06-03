<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\EventDispatcher\Tests\Service;

class BroadcastServiceProvider extends Service
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        Broadcast::channel('message_for_.*', function ($user, $chat_id) {
            return true;
        });
    }
}
