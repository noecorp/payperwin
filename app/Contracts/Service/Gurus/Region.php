<?php namespace App\Contracts\Service\Gurus;

interface Region {

	/**
	 * Returns list of valid regions.
	 *
	 * @return array
	 */
	public function regions();

}