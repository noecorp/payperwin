<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector as Redirect;

class RedirectIfAuthenticated {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The Redirector implementation.
	 *
	 * @var Redirect
	 */
	protected $redirect;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth, Redirect $redirect)
	{
		$this->auth = $auth;
		$this->redirect = $redirect;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->auth->check())
		{
			$user = $this->auth->user();

			if ($user->streamer)
			{
				if ($user->twitch_id && $user->summoner_id)
				{
					return $this->redirect->intended('/streamers/'.$user->id);
				}
				else
				{
					return $this->redirect->intended('/users/'.$user->id.'/edit');
				}
			}
			else
			{
				return $this->redirect->to('/streamers');
			}
		}

		return $next($request);
	}

}
