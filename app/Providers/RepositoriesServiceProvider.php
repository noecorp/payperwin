<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Repository\Aggregations as AggregationsInterface;
use App\Repositories\Aggregations;

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

		$this->bindMatches();

		$this->bindTransactions();

		$this->bindAggregations();
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

	protected function bindMatches()
	{
		$this->app->singleton(
			'App\Contracts\Repository\Matches',
			'App\Repositories\Matches'
		);
	}

	protected function bindTransactions()
	{
		$this->app->singleton(
			'App\Contracts\Repository\Transactions',
			'App\Repositories\Transactions'
		);
	}

	protected function bindAggregations()
	{
		$this->app->singleton(
			AggregationsInterface::class,
			Aggregations::class
		);
	}
}
