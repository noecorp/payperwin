<?php namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Auth\Guard;

class Wildcard {

	/**
	 * The authentication service implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new wildcard view composer.
	 *
	 * @param Guard $auth
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
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
	}

}
