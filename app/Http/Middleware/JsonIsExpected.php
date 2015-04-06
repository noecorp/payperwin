<?php namespace App\Http\Middleware;

use Closure;

class JsonIsExpected {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$request->expectsJson = true;
		
		return $next($request);
	}

}
