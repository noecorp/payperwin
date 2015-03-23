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

		$this->bindGurus();

		$this->bindApis();

		$this->bindServices();
	}

	protected function bindGurus()
	{
		$this->app->singleton(
			'App\Contracts\Service\Gurus\Pledge',
			'App\Services\Gurus\Pledge'
		);

		$this->app->singleton(
			'App\Contracts\Service\Gurus\Transaction',
			'App\Services\Gurus\Transaction'
		);
	}

	protected function bindApis()
	{
		$this->app->singleton(
			'App\Contracts\Service\GameApi\League\Client',
			'App\Services\GameApi\League\Client'
		);

		$this->app->bind(
			'App\Contracts\Service\GameApi\Player',
			'App\Services\GameApi\Player'
		);

		$this->app->bind(
			'App\Contracts\Service\GameApi\League\Match',
			'App\Services\GameApi\League\Match'
		);
	}

	protected function bindServices()
	{
		$this->app->singleton(
			'App\Contracts\Service\Acidifier',
			'App\Services\Acidifier'
		);

		$this->app->singleton(
			'App\Contracts\Service\Payments',
			'App\Services\Payments'
		);

		$this->app->singleton(
			'App\Contracts\Service\Distribution',
			'App\Services\Distribution'
		);
	}
}
