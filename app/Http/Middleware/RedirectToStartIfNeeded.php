<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Redirector as Redirect;

class RedirectToStartIfNeeded {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Redirector implementation.
	 *
	 * @var Redirect
	 */
	protected $redirect;

	/**
	 * Create a new filter instance.
	 *
	 * @param Guard  $auth
	 * @param Redirect $redirect
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
		if (!$this->auth->user()->start_completed)
		{
			return $this->redirect->to('start');
		}

		return $next($request);
	}

}
