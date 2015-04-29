<?php namespace App\Services;

use App\Contracts\Service\Acidifier as AcidifierInterface;
use Illuminate\Database\DatabaseManager;
use Closure;

class Acidifier implements AcidifierInterface {

	/**
	 * Database Manager implementation.
	 *
	 * @var DatabaseManager
	 */
	protected $db;

	/**
	 * Create a new Acidifier instance.
	 *
	 * @param DatabaseManager $db
	 *
	 * @return void
	 */
	public function __construct(DatabaseManager $db)
	{
		$this->db = $db;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transaction(Closure $closure)
	{
		return $this->db->transaction($closure);
	}

}
