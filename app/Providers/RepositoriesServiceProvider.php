<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider extends ServiceProvider {

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
		$this->bindUsers();

		$this->bindPledges();

		$this->bindDeposits();
	}

	protected function bindUsers()
	{
		$this->app->singleton(
			'App\Contracts\Repository\Users',
			'App\Repositories\Users'
		);
	}

	protected function bindPledges()
	{
		$this->app->singleton(
			'App\Contracts\Repository\Pledges',
			'App\Repositories\Pledges'
		);
	}

	protected function bindDeposits()
	{
		$this->app->singleton(
			'App\Contracts\Repository\Deposits',
			'App\Repositories\Deposits'
		);
	}
}
