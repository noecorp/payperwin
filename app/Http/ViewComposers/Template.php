<?php namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use App\Contracts\Repository\Users;

class Template {

	/**
	 * The Users Repository implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Create a new wildcard view composer.
	 *
	 * @param Users $users
	 */
	public function __construct(Users $users)
	{
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
		$view->with('streamersLiveNow', $this->users->isStreamer()->isLive()->count());
	}

}
