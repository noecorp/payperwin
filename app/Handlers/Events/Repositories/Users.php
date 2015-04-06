<?php namespace App\Handlers\Events\Repositories;

use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Events\Repositories\UsersWereCreated;
use App\Events\Repositories\UsersWereUpdated;
use Illuminate\Events\Dispatcher;

class Users {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
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
	 * @param  Dispatcher  $events
	 * @return array
	 */
	public function subscribe(Dispatcher $events)
	{
		$events->listen('App\Events\Repositories\UserWasCreated', 'App\Handlers\Events\Repositories\Users@onUserWasCreated');
		$events->listen('App\Events\Repositories\UsersWereCreated', 'App\Handlers\Events\Repositories\Users@onUsersWereCreated');
		$events->listen('App\Events\Repositories\UserWasUpdated', 'App\Handlers\Events\Repositories\Users@onUserWasUpdated');
		$events->listen('App\Events\Repositories\UsersWereUpdated', 'App\Handlers\Events\Repositories\Users@onUsersWereUpdated');
	}

}