<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Handlers\Events\Repositories\Users;
use App\Handlers\Events\Services\GameApi;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'SocialiteProviders\Manager\SocialiteWasCalled' => [
			'SocialiteProviders\Twitch\TwitchExtendSocialite@handle'
		]
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

		$events->subscribe(Users::class);

		$events->subscribe(GameApi::class);
	}

}
