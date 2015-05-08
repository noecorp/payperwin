<?php namespace App\Commands;

use Illuminate\Contracts\Cache\Repository as Cache;
use Closure;
use Exception;
use Serializable;

abstract class Command {

	/**
	 * Whether or not this command needs a uniqueness check
	 * before running.
	 *
	 * Used to mitigate race conditions.
	 *
	 * @var boolean
	 */
	protected $unique = true;

	/**
	 * Unique identifier for this specific command instance.
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * Cache repository implementation.
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$args = func_get_args();
		
		$suffix = '';

		foreach ($args as $arg)
		{
			if (is_array($arg))
			{
				$suffix .= serialize($arg);
			}
			else
			{
				$suffix .= (string) $arg;
			}
		}

		$this->identifier = static::class . '-' . $suffix;
	}

	/**
	 * Trigger the command.
	 *
	 * We use this as an intermediary to make sure uniqueness checks take place.
	 *
	 * @return void
	 */
	protected function start()
	{
		if ($this->unique)
		{
			if (!isset($this->cache) || !$this->cache instanceof Cache)
			{
				throw new \Exception("Command's cache repository not set.");
			}

			if (!isset($this->identifier))
			{
				throw new \Exception("Command's unique identifier not set.");
			}
		}

		try
		{
			if ($this->unique)
			{
				if ($this->cache->has($this->identifier))
				{
					$this->delete();
					return;	
				}

				$this->cache->put($this->identifier, true, 1);
			}

			$this->work();

			if ($this->unique)
			{
				$this->cache->forget($this->identifier);
			}
		}
		catch (\Exception $e)
		{
			if ($this->unique)
			{
				$this->cache->forget($this->identifier);
			}

			throw $e;
		}
	}

	/**
	 * The actual command logic.
	 *
	 * @return void
	 */
	abstract protected function work();

}
