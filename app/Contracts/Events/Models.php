<?php namespace App\Contracts\Events;

interface Models {
	
	/**
	 * Get the model ids associated with the event.
	 *
	 * @return int
	 */
	public function ids();

}