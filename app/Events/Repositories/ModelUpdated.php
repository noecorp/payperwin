<?php namespace App\Events\Repositories;

use App\Events\Event;
use App\Models\Model;
use App\Contracts\Events\ModelUpdated as ModelUpdatedEventInterface;

abstract class ModelUpdated extends ModelEvent implements ModelUpdatedEventInterface {

	/**
	 * The model's changed attributes.
	 *
	 * @var array
	 */
	protected $changed;

	/**
	 * Create a new event instance.
	 *
	 * @param Model $model
	 * @param array $changed
	 *
	 * @return void
	 */
	public function __construct(Model $model, array $changed)
	{
		parent::__construct($model);

		$this->changed = $changed;
	}

	/**
	 * {@inheritdoc}
	 */
	public function changed()
	{
		return $this->changed;
	}

}
