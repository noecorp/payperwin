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
	 * Fetch the model that has the specified id.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function havingId($id);

	public function update($id, array $data);
	
}