<?php namespace App\Http;

use App\Http\Middleware\PaypalVerifyIPN;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\RedirectToStartIfNeeded;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'App\Http\Middleware\VerifyCsrfToken',
		'App\Http\Middleware\TrackAffiliateId',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'App\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
		'own.user' => 'App\Http\Middleware\OwnsUserResource',
		'own.pledge' => 'App\Http\Middleware\OwnsPledgeResource',
		'ajax' => 'App\Http\Middleware\OnlyRespondIfAjax',
		'json' => 'App\Http\Middleware\JsonIsExpected',
		'paypal.verify.ipn' => PaypalVerifyIPN::class,
		'start' => RedirectToStartIfNeeded::class,
	];

}
