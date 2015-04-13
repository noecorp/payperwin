<?php namespace App\Contracts\Service\GameApi;

interface Player {

	/**
	 * Create a Player instance.
	 *
	 * @param int $id
	 * @param string $name
	 *
	 * @return static
	 */
	public function create($id, $name);

	/**
	 * Get the player's id.
	 *
	 * @return int
	 */
	public function id();

	/**
	 * Get the player's name.
	 *
	 * @return string
	 */
	public function name();

}
