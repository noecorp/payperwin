<?php namespace App\Services;

use App\Contracts\Service\Acidifier as AcidifierInterface;
use Illuminate\Database\ConnectionInterface;
use Closure;

class Acidifier implements AcidifierInterface {

	/**
	 * Database connection implementation.
	 *
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * Perform a database transaction.
	 *
	 * @param ConnectionInterface $connection
	 *
	 * @return void
	 */
	public function __construct(ConnectionInterface $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transaction(Closure $closure)
	{
		return $this->connection->transaction($closure);
	}

}