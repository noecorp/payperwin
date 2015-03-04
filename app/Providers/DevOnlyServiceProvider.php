<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DevOnlyServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ( $this->app->environment('local') || $this->app->environment('staging'))
		{
			$this->app->register('Barryvdh\Debugbar\ServiceProvider');

			$this->app->register('App\Providers\TestingServiceProvider');
		}
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
