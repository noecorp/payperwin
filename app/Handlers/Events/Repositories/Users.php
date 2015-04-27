<?php namespace App\Handlers\Events\Repositories;

use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Events\Repositories\UsersWereCreated;
use App\Events\Repositories\UsersWereUpdated;

use App\Contracts\Events\Model;
use App\Contracts\Events\Models;
use App\Contracts\Repository\Users as UsersRepository;
use Illuminate\Session\SessionManager as Session;
use App\Contracts\Service\Shortener;

use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Bus\QueueingDispatcher as Dispatcher;
use App\Commands\NotifyAboutNewStreamer;
use App\Commands\AggregateDataFromUserUpdate;
use App\Commands\SendEmailConfirmationRequest;

class Users {

	/**
	 * Command dispatcher implementation.
	 *
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Users repository implementation.
	 *
	 * @var UsersRepository
	 */
	protected $users;

	/**
	 * Session manager implementation.
	 *
	 * @var Session
	 */
	protected $session;

	/**
	 * URL Shortener implementation.
	 *
	 * @var Shortener
	 */
	protected $shorten;

	/**
	 * Create the event handler.
	 *
	 * @param Dispatcher $dispatcher
	 * @param UsersRepository $users
	 * @param Session $session
	 * @param Shortener $shorten
	 *
	 * @return void
	 */
	public function __construct(Dispatcher $dispatcher, UsersRepository $users, Session $session, Shortener $shorten)
	{
		$this->dispatcher = $dispatcher;
		$this->users = $users;
		$this->session = $session;
		$this->shorten = $shorten;
	}

	/**
	 * Handle single user creation events.
	 *
	 * @param Model $event
	 */
	public function onUserWasCreated(Model $event)
	{
		$user = $event->model();

		if ($this->session->has('auid'))
		{
			$referrer = $this->users->find($this->session->get('auid'));

			if ($referrer)
			{
				$this->users->update($user, ['referred_by' => $referrer->id]);
			}
		}

		if (!$user->email_confirmed)
		{
			$this->dispatcher->dispatchToQueue(new SendEmailConfirmationRequest($user->id));
		}
	}

	/**
	 * Handle single user update events.
	 *
	 * @param Model $event
	 */
	public function onUserWasUpdated(Model $event)
	{
		$user = $event->model();

		$changed = $event->changed();

		if (isset($changed['earnings']) || isset($changed['funds']))
		{
			$this->dispatcher->dispatchToQueue(new AggregateDataFromUserUpdate($user->id, $changed, $user->getOriginal()));
		}

		if ($user->streamer && $user->twitch_id && $user->summoner_id && !$user->streamer_completed)
		{
			$this->users->update($user, ['streamer_completed' => true, 'start_completed' => true]);

			$url = $this->shorten->url(app_url('streamers',[$user->id]), $user->username);

			if ($url)
			{
				$this->users->update($user, ['short_url' => $url]);
			}

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
