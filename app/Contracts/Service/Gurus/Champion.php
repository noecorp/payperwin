<?php namespace App\Contracts\Service\Gurus;

interface Champion {

	/**
	 * Returns list of Champion types.
	 *
	 * @return array
	 */
	public function types();

	/**
	 * Returns the Champion name associated with an id.
	 *
	 * @return int
	 */
	public function name($id);

}
