<?php namespace App\Contracts\Repository;

interface RepositoryContract {

	/**
	 * Create and store an instance of the repository's model.
	 *
	 * @param array $data
	 *
	 * @return \Illuminate\Database\Eloquent\Model
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
	 * @param int|\Illuminate\Database\Eloquent\Model $id Id of the model to update or the actual model object.
	 * @param array $data
	 * @param boolean $return Whether or not to return the model object after updating.
	 *
	 * @return \Illuminate\Database\Eloquent\Model|void
	 */
	public function update($id, array $data, $return = true);

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
	 * Increment by 1 the specified property for the given ids.
	 *
	 * @param array $ids
	 * @param string $column
	 *
	 * @return void
	 */
	public function incrementAll(array $ids, $column);

	/**
	 * Fetch the model that has the specified id or the first model from the query.
	 *
	 * @param int $id Optional
	 *
	 * @return \Illuminate\Database\Eloquent\Model|null
	 */
	public function find($id = null);

	/**
	 * Fetch the models based on provided repository specifications.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function all();

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
	 * @param Carbon|DateTime|string $date
	 *
	 * @return static
	 */
	public function after($date);

	/**
	 * Constrain query to results created before a certain date.
	 *
	 * @param Carbon|DateTime|string $date
	 *
	 * @return static
	 */
	public function before($date);

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