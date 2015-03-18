<?php namespace App\Repositories;

use App\Contracts\Repository\RepositoryContract;
use Illuminate\Contracts\Cache\Repository as Cache;
use Carbon\Carbon;

abstract class AbstractRepository implements RepositoryContract {

	/**
	 * A clean model instance specific to this repository.
	 *
	 * Used to build queries.
	 *
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	protected $model;

	/**
	 * Current query instance.
	 *
	 * @var \Illuminate\Database\Eloquent\Builder
	 */
	protected $query;

	/**
	 * Create a new instance of the repository.
	 *
	 * @param Cache $cache
	 */
	public function __construct(Cache $cache)
	{
		$this->model = $this->model();
		$this->cache = $cache;
	}

	/**
	 * Get a clean model instance specific to this repository.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	abstract protected function model();

	/**
	 * Get the current query or create a new one.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function query()
	{
		if (!$this->query) $this->query = $this->model->newQuery();

		return $this->query;
	}

	/**
	 * Reset the current query to allow for clean, future queries on this repository.
	 */
	protected function reset()
	{
		$this->query = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(array $data)
	{
		$model = $this->model->newInstance($data);

		$model->save();

		$this->cache->tags($this->model->getTable())->flush();

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update($id, array $data)
	{
		$model = $this->model->newQuery()->findOrFail($id);

		$model->fill($data);
		
		if ($model->isDirty())
		{
			$model->save(); 

			$this->cache->tags($this->model->getTable())->flush();
		}

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($id = null)
	{
		if ($id)
		{
			$this->query()->whereId($id);
		}
		else
		{
			$this->query()->limit(1);
		}

		$hash = $this->getQueryHash();

		$tags = $this->getCacheTags();

		$model = $this->cache->tags($tags)->rememberForever($hash, function()
		{
		    return $this->query()->first();
		});
		
		$this->reset();

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all()
	{
		$hash = $this->getQueryHash();

		$tags = $this->getCacheTags();

		$results = $this->cache->tags($tags)->rememberForever($hash, function()
		{
		    return $this->query()->get();
		});

		$this->reset();

		return $results;
	}

	/**
	 * {@inheritdoc}
	 */
	public function latest()
	{
		$this->query()->orderBy('created_at','desc');

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function earliest()
	{
		$this->query()->orderBy('created_at','asc');
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function after($date)
	{
		$this->query()->where('created_at','>',new Carbon($date));
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function before($date)
	{
		$this->query()->where('created_at','<',new Carbon($date));
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function limit($take, $page = null)
	{
		if ($take > 0)
		{
			$this->query()->limit($take);
		}
		
		if ($page > 1)
		{
			$this->query()->skip($take * ($page-1));
		}

		return $this;
	}

	/**
	 * Get a list of the required cache tags for the current query.
	 *
	 * @return array
	 */
	protected function getCacheTags()
	{
		$relations = array_keys($this->query()->getEagerLoads());

		$tags = [$this->model->getTable()];

		foreach ($relations as $relation)
		{
			$tags[] = $this->query()->getRelation($relation)->getRelated()->getTable();
		}

		sort($tags);
		
		return array_unique($tags);
	}

	/**
	 * Get a unique hash representation for the current query.
	 *
	 * @return string
	 */
	protected function getQueryHash()
	{
		$hash = $this->query()->getQuery()->toSql() . implode(',', $this->query()->getBindings());

		$relations = array_keys($this->query()->getEagerLoads());

		// Order doesn't actually matter, but it WOULD change the hash.
		sort($relations);
		
		foreach ($relations as $key)
		{
			$relation = $this->query()->getRelation($key);
			$hash .= $relation->toSql() . implode(',', $relation->getBindings());
		}

		return md5($hash);
	}

}