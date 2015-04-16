<?php namespace App\Handlers\Events\Repositories;

use App\Events\Repositories\AggregationWasCreated;
use App\Events\Repositories\AggregationWasUpdated;
use App\Events\Repositories\AggregationsWereCreated;
use App\Events\Repositories\AggregationsWereUpdated;

use App\Contracts\Events\Model;
use App\Contracts\Events\Models;

use Illuminate\Contracts\Events\Dispatcher as Events;

class Aggregations {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle single pledge creation events.
	 *
	 * @param Model $event
	 */
	public function onAggregationWasCreated(Model $event)
	{

	}

	/**
	 * Handle single pledge update events.
	 *
	 * @param Model $event
	 */
	public function onAggregationWasUpdated(Model $event)
	{

	}

	/**
	 * Handle mass pledge creation events.
	 *
	 * @param Models $event
	 */
	public function onAggregationsWereCreated(Models $event)
	{

	}

	/**
	 * Handle single pledge update events.
	 *
	 * @param Models $event
	 */
	public function onAggregationsWereUpdated(Models $event)
	{

	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Events  $events
	 * @return array
	 */
	public function subscribe(Events $events)
	{
		$events->listen(AggregationWasCreated::class, 'App\Handlers\Events\Repositories\Aggregations@onAggregationWasCreated');
		$events->listen(AggregationsWereUpdated::class, 'App\Handlers\Events\Repositories\Aggregations@onAggregationsWereCreated');
		$events->listen(AggregationWasUpdated::class, 'App\Handlers\Events\Repositories\Aggregations@onAggregationWasUpdated');
		$events->listen(AggregationsWereUpdated::class, 'App\Handlers\Events\Repositories\Aggregations@onAggregationsWereUpdated');
	}

}
