<?php namespace App\Handlers\Events\Repositories;

use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Events\Repositories\UsersWereCreated;
use App\Events\Repositories\UsersWereUpdated;

use App\Contracts\Events\Model;
use App\Contracts\Events\Models;

use Illuminate\Events\Dispatcher as Events;
use Illuminate\Contracts\Bus\QueueingDispatcher as Dispatcher;
use App\Models\User;
use App\Commands\NotifyAboutNewStreamer;

class Users {

	/**
	 * Create the event handler.
	 *
	 * @param Dispatcher $dispatcher
	 *
	 * @return void
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Handle single user creation events.
	 *
	 * @param Model $event
	 */
	public function onUserWasCreated(Model $event)
	{

	}

	/**
	 * Handle single user update events.
	 *
	 * @param Model $event
	 */
	public function onUserWasUpdated(Model $event)
	{
		$user = $event->model();

		if ($user->streamer && $user->twitch_id && $user->summoner_id && !$user->short_url)
		{
			$this->dispatcher->dispatchToQueue(new NotifyAboutNewStreamer($user->id));
		}
	}

	/**
	 * Handle mass user creation events.
	 *
	 * @param Models $event
	 */
	public function onUsersWereCreated(Models $event)
	{

	}

	/**
	 * Handle single user update events.
	 *
	 * @param Models $event
	 */
	public function onUsersWereUpdated(Models $event)
	{

	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Events  $events
	 * @return array
	 */
	public function subscribe(Events $events)
	{
		$events->listen(UserWasCreated::class, 'App\Handlers\Events\Repositories\Users@onUserWasCreated');
		$events->listen(UsersWereCreated::class, 'App\Handlers\Events\Repositories\Users@onUsersWereCreated');
		$events->listen(UserWasUpdated::class, 'App\Handlers\Events\Repositories\Users@onUserWasUpdated');
		$events->listen(UsersWereUpdated::class, 'App\Handlers\Events\Repositories\Users@onUsersWereUpdated');
	}

}