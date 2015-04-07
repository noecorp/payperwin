<?php namespace App\Handlers\Events\Repositories;

use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Events\Repositories\UsersWereCreated;
use App\Events\Repositories\UsersWereUpdated;
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
	 * @param UserWasCreated $event
	 */
	public function onUserWasCreated(UserWasCreated $event)
	{

	}

	/**
	 * Handle single user update events.
	 *
	 * @param UserWasUpdated $event
	 */
	public function onUserWasUpdated(UserWasUpdated $event)
	{
		$user = $event->model();

		if ($user->streamer && $user->twitch_id && $user->summoner_id && !$user->short_url)
		{
			$this->dispatcher->dispatchToQueue($this->commandForNotifyingAboutNewStreamer($user));
		}
	}

	/**
	 * Handle mass user creation events.
	 *
	 * @param UserWasCreated $event
	 */
	public function onUsersWereCreated(UsersWereCreated $event)
	{

	}

	/**
	 * Handle single user update events.
	 *
	 * @param UserWasUpdated $event
	 */
	public function onUsersWereUpdated(UsersWereUpdated $event)
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
		$events->listen('App\Events\Repositories\UserWasCreated', 'App\Handlers\Events\Repositories\Users@onUserWasCreated');
		$events->listen('App\Events\Repositories\UsersWereCreated', 'App\Handlers\Events\Repositories\Users@onUsersWereCreated');
		$events->listen('App\Events\Repositories\UserWasUpdated', 'App\Handlers\Events\Repositories\Users@onUserWasUpdated');
		$events->listen('App\Events\Repositories\UsersWereUpdated', 'App\Handlers\Events\Repositories\Users@onUsersWereUpdated');
	}

	protected function commandForNotifyingAboutNewStreamer(User $streamer)
	{
		return new NotifyAboutNewStreamer($streamer->id);
	}

}