<?php namespace App\Events\Repositories;

use App\Events\Event;
use App\Models\Model;
use App\Contracts\Events\Model as ModelEventInterface;

abstract class ModelEvent extends Event implements ModelEventInterface {

	/**
	 * Model instance associated with the event.
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Create a new event instance.
	 *
	 * @param Model $model
	 *
	 * @return void
	 */
	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function model()
	{
		return $this->model;
	}

}
