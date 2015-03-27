<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Contracts\Repository\Pledges;

class OwnsPledgeResource {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The Pledges Repository implementation.
	 *
	 * @var UsersRepository
	 */
	protected $pledges;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth, Pledges $pledges)
	{
		$this->auth = $auth;
		$this->pledges = $pledges;
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
		if ($this->auth->user())
		{
			$pledgeId = (int) $request->route()->parameter('pledges');

			$pledge = $this->pledges->find($pledgeId);

			if ($pledge && $this->auth->user()->id != $pledge->user_id)
			{
				return abort(401);
			}
		}

		return $next($request);
	}

}
