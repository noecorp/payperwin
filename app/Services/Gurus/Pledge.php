<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Pledge as PledgeGuruInterface;

class Pledge implements PledgeGuruInterface {

	const Win = 1;

	/**
	 * List of all valid Pledge types.
	 *
	 * @var array
	 */
	protected $types = [
		self::Win => 'win',
	];

	/**
	 * {@inheritdoc}
	 */
	public function types()
	{
		return array_keys($this->types);
	}

	/**
	 * {@inheritdoc}
	 */
	public function type($type)
	{
		return $this->types[$type];
	}

	/**
	 * {@inheritdoc}
	 */
	public function win()
	{
		return self::Win;
	}

}