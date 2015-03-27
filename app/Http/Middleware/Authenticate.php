<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The Response Factory implementation.
	 *
	 * @var Guard
	 */
	protected $response;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @param  Response  $response
	 * @return void
	 */
	public function __construct(Guard $auth, Response $response)
	{
		$this->auth = $auth;
		$this->response = $response;
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
		if ($this->auth->guest())
		{
			if ($request->ajax())
			{
				return $this->response->json(['redirect'=>url('auth/login')],302,['Location'=>url('auth/login')]);
			}
			else
			{
				return $this->response->make('',302,['Location'=>url('auth/login')]);
			}
		}

		return $next($request);
	}

}
