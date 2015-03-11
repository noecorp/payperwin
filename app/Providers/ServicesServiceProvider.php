<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServicesServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(
			'App\Contracts\Service\PledgeGuru',
			'App\Services\PledgeGuru'
		);

		$this->app->singleton(
			'App\Contracts\Service\Acidifier',
			'App\Services\Acidifier'
		);

		$this->app->singleton(
			'App\Contracts\Service\Payments',
			'App\Services\Payments'
		);
	}

}
