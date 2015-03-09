<?php namespace App\Services;

use App\Contracts\Service\PledgeKeeper as PledgeKeeperInterface;

class PledgeKeeper implements PledgeKeeperInterface {

	/**
	 * List of all valid Pledge types.
	 *
	 * @var array
	 */
	protected $types = [
		'win' => 1,
	];

	/**
	 * {@inheritdoc}
	 */
	public function types()
	{
		return array_values($this->types);
	}

	/**
	 * {@inheritdoc}
	 */
	public function win()
	{
		return $this->types['win'];
	}

}