<?php namespace App\Contracts\Repository;

use App\Models\Model;
use \DateTime;

interface RepositoryContract {

	/**
	 * Run the query without generating any model events.
	 *
	 * @return static
	 */
	public function quietly();

	/**
	 * Get results directly from the database, without using the cache.
	 *
	 * @return static
	 */
	public function freshly();

	/**
	 * Create and store an instance of the repository's model.
	 *
	 * @param array $data
	 *
	 * @return Model
	 */
	public function create(array $data);

	/**
	 * Create models from all provided data.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function createAll(array $data);

	/**
	 * Update and return the stored model based on id with the provided data.
	 *
	 * @param Model $model The model to update.
	 * @param array $data
	 *
	 * @return Model
	 */
	public function update(Model $model, array $data);

	/**
	 * Update all stored models based on ids with the provided data.
	 *
	 * @param array $ids
	 * @param array $data
	 *
	 * @return void
	 */
	public function updateAll(array $ids, array $data);

	/**
	 * Increment by 1 the specified property for the given model id.
	 *
	 * @param Model $model
	 * @param string $column
	 * @param float $amount
	 *
	 * @return void
	 */
	public function increment(Model $model, $column, $amount = 1.0);

	/**
	 * Increment by 1 the specified property for the given ids.
	 *
	 * @param array $ids
	 * @param string $column
	 * @param float $amount
	 *
	 * @return void
	 */
	public function incrementAll(array $ids, $column, $amount=1.0);

	/**
	 * Fetch the model that has the specified id or the first model from the query.
	 *
	 * @param int $id Optional
	 *
	 * @return Model|null
	 */
	public function find($id = null);

	/**
	 * Fetch the models based on provided repository specifications.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function all();

	/**
	 * Count the number of results for the query.
	 *
	 * @param string $column
	 *
	 * @return int
	 */
	public function count($column = 'id');
	
	/**
	 * Calculate the average value for a column.
	 *
	 * @param string $column
	 *
	 * @return float
	 */
	public function average($column);

	/**
	 * Order query results by latest first.
	 *
	 * @return static
	 */
	public function latest();

	/**
	 * Order query results by earliest first.
	 *
	 * @return static
	 */
	public function earliest();

	/**
	 * Constrain query to results created after a certain date.
	 *
	 * @param Carbon|DateTime $date
	 *
	 * @return static
	 */
	public function after(DateTime $date);

	/**
	 * Constrain query to results created before a certain date.
	 *
	 * @param Carbon|DateTime $date
	 *
	 * @return static
	 */
	public function before(DateTime $date);

	/**
	 * Add limit and offset to the query.
	 *
	 * @param int $take Row limit
	 * @param int $page Results page to start from (default 1)
	 *
	 * @return static
	 */
	public function limit($take, $page = null);
	
}
