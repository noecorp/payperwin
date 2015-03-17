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
	 * Update and return the stored model based on id with the provided data.
	 *
	 * @param int $id
	 * @param array $data
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function update($id, array $data);

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