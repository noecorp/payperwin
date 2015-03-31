<?php namespace App\Contracts\Service\Gurus;

interface Pledge {

	/**
	 * Returns list of Pledge types.
	 *
	 * @return array
	 */
	public function types();

	/**
	 * Returns Pledge type name for the given type id.
	 *
	 * @return string
	 */
	public function type($type);

	/**
	 * Returns the Pledge type associated with a win.
	 *
	 * @return int
	 */
	public function win();

}