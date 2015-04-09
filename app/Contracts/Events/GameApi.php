<?php namespace App\Contracts\Events;

interface GameApi {

	/**
	 * Get the game that the event is for.
	 *
	 * @return string
	 */
	public function game();

	/**
	 * Get the url that the event is for.
	 *
	 * @return string
	 */
	public function url();

	/**
	 * Get the additional info for the event.
	 *
	 * @return array
	 */
	public function info();

}