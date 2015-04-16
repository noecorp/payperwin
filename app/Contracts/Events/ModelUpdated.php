<?php namespace App\Contracts\Events;

interface ModelUpdated extends Model {

	/**
	 * Get the changed attributes.
	 *
	 * @return array
	 */
	public function changed();

}
