<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Pledge as PledgeGuruInterface;

class Pledge implements PledgeGuruInterface {

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