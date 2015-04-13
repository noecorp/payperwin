<?php namespace App\Contracts\Service\GameApi\League;

interface Match {

	/**
	 * Create a match instance from the given data for the given player.
	 *
	 * @param array $data
	 * @param int $playerId
	 *
	 * @return static|null
	 */
	public function createForPlayerId(array $data,$playerId);

	/**
	 * Get the matches's id.
	 *
	 * @return int
	 */
	public function id();

	/**
	 * Get the matches's creation timestamp.
	 *
	 * @return int
	 */
	public function timestamp();

	/**
	 * Whether or not the match was won.
	 *
	 * @return boolean
	 */
	public function win();

	/**
	 * Get the champion played.
	 *
	 * @return int
	 */
	public function champion();

	/**
	 * Get the number of player kills.
	 *
	 * @return int
	 */
	public function kills();

	/**
	 * Get the number of player assists.
	 *
	 * @return int
	 */
	public function assists();

	/**
	 * Get the number of player deaths.
	 *
	 * @return int
	 */
	public function deaths();

}
