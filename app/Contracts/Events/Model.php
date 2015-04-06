<?php namespace App\Contracts\Events;

interface Model {

	/**
	 * Get the model associated with the event.
	 *
	 * @return \App\Models\Model
	 */
	public function model();

}