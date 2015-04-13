<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\SessionManager as Session;
use Illuminate\Routing\Redirector as Redirect;

class TrackAffiliateId {

	/**
	 * The Session Factory implementation.
	 *
	 * @var Guard
	 */
	protected $session;

	/**
	 * The Redirector implementation.
	 *
	 * @var Redirect
	 */
	protected $redirect;

	/**
	 * Create a new filter instance.
	 *
	 * @param Session $session
	 * @return void
	 */
	public function __construct(Session $session, Redirect $redirect)
	{
		$this->session = $session;
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
		if ($request->has('auid'))
		{
			$this->session->put('auid',$request->get('auid'));

			if (strtolower($request->method()) == 'get')
			{
				$query = $request->query();

				unset($query['auid']);

				$args = empty($query) ? '' : '/?'.http_build_query($query);

				return $this->redirect->to($request->url().$args);
			}
		}

		return $next($request);
	}

}
