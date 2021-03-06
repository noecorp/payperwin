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
		if ( !$this->app->environment('production') )
		{
			$this->app->register('Barryvdh\Debugbar\ServiceProvider');
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
