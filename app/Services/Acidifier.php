<?php namespace App\Services;

use App\Contracts\Service\Acidifier as AcidifierInterface;
use Illuminate\Contracts\Container\Container;
use Closure;

class Acidifier implements AcidifierInterface {

	/**
	 * Application container implementation.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Create a new Acidifier instance.
	 *
	 * @param Container $container
	 *
	 * @return void
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transaction(Closure $closure)
	{
		return $this->container->make('db')->transaction($closure);
	}

}
