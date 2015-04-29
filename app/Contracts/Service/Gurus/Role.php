<?php namespace App\Contracts\Service\Gurus;

interface Role {

	/**
	 * Return the Role id for an Admin.
	 *
	 * @return int|null
	 */
	public function admin();

}
