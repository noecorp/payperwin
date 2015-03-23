<?php namespace App\Contracts\Service\Gurus;

interface Pledge {

	/**
	 * Returns list of Pledge types.
	 *
	 * @return array
	 */
	public function types();

	/**
	 * Returns the Pledge type associated with a win.
	 *
	 * @return int
	 */
	public function win();

}