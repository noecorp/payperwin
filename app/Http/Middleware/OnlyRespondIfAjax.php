<?php namespace App\Http\Middleware;

use Closure;

class OnlyRespondIfAjax {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (!$request->ajax())
		{
			return abort(400);
		}

		return $next($request);
	}

}
