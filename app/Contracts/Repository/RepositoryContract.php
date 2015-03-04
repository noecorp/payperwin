<?php namespace App\Contracts\Repository;

interface RepositoryContract {

	/**
	 * Create and store an instance of the repository's implied model.
	 *
	 * @param array $data
	 */
	public function create(array $data);

	/**
	 * Fetch the model that has the specified id.
	 *
	 * @param int $id
	 */
	public function withId($id);
	
}