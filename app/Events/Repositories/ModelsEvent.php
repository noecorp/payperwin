<?php namespace App\Events\Repositories;

use App\Events\Event;
use App\Contracts\Events\Models as ModelsEventInterface;

abstract class ModelsEvent extends Event implements ModelsEventInterface {

	/**
	 * Model ids associated with the event.
	 *
	 * @var int
	 */
	protected $ids;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(array $models)
	{
		$this->ids = $models;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ids()
	{
		return $this->ids;
	}

}
