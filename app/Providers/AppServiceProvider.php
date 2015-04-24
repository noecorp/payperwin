<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use App\Extensions\Auth\RepositoryUserProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(Response $response)
	{
		$this->app['auth']->extend('repository', function()
		{
			return $this->app->make(RepositoryUserProvider::class);
		});
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(RepositoryUserProvider::class, RepositoryUserProvider::class);
	}

}
