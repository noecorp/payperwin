<?php namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Auth\Guard;
use App\Contracts\Repository\Users;

class Wildcard {

	/**
	 * The authentication service implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The Users Repository implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Create a new wildcard view composer.
	 *
	 * @param  Guard  $auth
	 */
	public function __construct(Guard $auth, Users $users)
	{
		$this->auth = $auth;
		$this->users = $users;
	}

	/**
	 * Bind data to the view.
	 *
	 * @param  View  $view
	 * @return void
	 */
	public function compose(View $view)
	{
		$view->with('auth', $this->auth);
		$view->with('streamersLiveNow', $this->users->isStreamer()->isLive()->count());
	}

}
