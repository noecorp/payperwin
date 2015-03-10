<?php namespace App\Repositories;

use App\Contracts\Repository\RepositoryContract;

abstract class AbstractRepository implements RepositoryContract {

	/**
	 * A clean model instance specific to this repository.
	 *
	 * Used to build queries.
	 *
	 * @var \Illuminate\Database\Eloquent\Model $model
	 */
	protected $model;

	/**
	 * Create a new instance of the repository.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 */
	public function __construct(\Illuminate\Database\Eloquent\Model $model)
	{
		$this->model = $model;
	}

	/**
	 * Get a new query instance.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function newQuery()
	{
		return $this->model->newQuery();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(array $data)
	{
		$model = $this->model->newInstance($data);

		$model->save();

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update($id, array $data)
	{
		$model = $this->model->newQuery()->findOrFail($id);

		$model->fill($data);

		$model->save();

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function havingId($id)
	{
		return $this->model->newQuery()->find($id);
	}

	/**
	 * Add limit and offset to the specified query.
	 *
	 * @param \Illuminate\Database\Query\Builder $query
	 * @param int $take Row limit
	 * @param int $page Results page to start from (default 1)
	 */
	protected function addLimits(\Illuminate\Database\Eloquent\Builder $query,$take,$page)
	{
		if ($take > 0)
		{
			$query->limit($take);
		}
		
		if ($page > 1)
		{
			$query->skip($take * ($page-1));
		}
	}

}