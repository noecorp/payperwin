<?php namespace App\Contracts\Service\GameApi;

interface Player {

	/**
	 * Set the player's id.
	 *
	 * @param string|int $id
	 * @param string $name
	 *
	 * @return static
	 */
	public function create($id, $name);

	/**
	 * Get the player's id.
	 *
	 * @return string|int
	 */
	public function id();

	/**
	 * Get the player's name.
	 *
	 * @return string
	 */
	public function name();

}