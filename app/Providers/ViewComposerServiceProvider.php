<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\View\Factory as View;

class ViewComposerServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot(View $view)
	{
		$view->composer('*', 'App\Http\ViewComposers\Wildcard');
		$view->composer('app', 'App\Http\ViewComposers\Template');
		$view->composer('deposits.paypalButton', 'App\Http\ViewComposers\PaypalButton');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

}
