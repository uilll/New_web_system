<?php namespace App\Providers;

use App\Listeners\NewMessageListener;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Carbon\Carbon;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'event.name' => [
			'EventListener',
		],
        'App\Events\NewMessage' => [
            NewMessageListener::class
        ],

	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);
		\Event::listen('auth.login', function ($user, $remember) {
			$user->last_login_at = Carbon::now();
			$user->save();

			session(['last_login_at' => $user->last_login_at]);
		});
		//
	}

}
