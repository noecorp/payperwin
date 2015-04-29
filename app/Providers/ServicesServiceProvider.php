<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Service\Gurus\Aggregation as AggregationGuruInterface;
use App\Services\Gurus\Aggregation as AggregationGuru;

use App\Contracts\Service\Gurus\Permission as PermissionGuruInterface;
use App\Services\Gurus\Permission as PermissionGuru;

use App\Contracts\Service\Gurus\Role as RoleGuruInterface;
use App\Services\Gurus\Role as RoleGuru;

use App\Contracts\Service\Shortener as ShortenerServiceInterface;
use App\Services\Shortener as ShortenerService;

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

		$this->app->singleton(
			'App\Contracts\Service\Gurus\Champion',
			'App\Services\Gurus\Champion'
		);

		$this->app->singleton(
			'App\Contracts\Service\Gurus\Region',
			'App\Services\Gurus\Region'
		);

		$this->app->singleton(
			AggregationGuruInterface::class,
			AggregationGuru::class
		);

		$this->app->singleton(
			PermissionGuruInterface::class,
			PermissionGuru::class
		);

		$this->app->singleton(
			RoleGuruInterface::class,
			RoleGuru::class
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
			'App\Contracts\Service\Distribution',
			'App\Services\Distribution'
		);

		$this->app->bind(
			ShortenerServiceInterface::class,
			ShortenerService::class
		);
	}
}
