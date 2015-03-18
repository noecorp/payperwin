<?php namespace App\Services\GameApi;

use App\Contracts\Service\GameApi\Player as PlayerInterface;

class Player implements PlayerInterface {

	/**
	 * The player's id.
	 *
	 * @var string|int
	 */
	protected $id;

	/**
	 * The player's name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * {@inheritdoc}
	 */
	public function create($id, $name)
	{
		$player = new static;

		$player->id = $id;
		$player->name = $name;

		return $player;
	}

	/**
	 * {@inheritdoc}
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function name()
	{
		return $this->name;
	}

}