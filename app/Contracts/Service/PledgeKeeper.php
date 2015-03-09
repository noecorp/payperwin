<?php namespace App\Contracts\Service;

interface PledgeKeeper {

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