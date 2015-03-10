<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Commands\MakeTest;

class TestingServiceProvider extends ServiceProvider {

	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
		$this->app->singleton('command.test.make', function($app)
		{
		    return new MakeTest($app['files']);
		});

		$this->commands('command.test.make');
	}

	/**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
        	'command.test.make',
        ];
    }

}
