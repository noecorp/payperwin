<?php namespace App\Repositories;

use App\Contracts\Repository\RepositoryContract;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Container\Container;
use Carbon\Carbon;
use App\Models\Model;
use App\Exceptions\Repositories\ModelClassMismatch;

abstract class AbstractRepository implements RepositoryContract {

	/**
	 * A clean model instance specific to this repository.
	 *
	 * Used to build queries.
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Current query instance.
	 *
	 * @var \Illuminate\Database\Eloquent\Builder
	 */
	protected $query;

	/**
	 * Cache repository instance
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * Events dispatcher instance
	 *
	 * @var Events
	 */
	protected $events;

	/**
	 * App container instance
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Create a new instance of the repository.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->model = $container->make($this->model());
		$this->cache = $container->make('Illuminate\Contracts\Cache\Repository');
		$this->events = $container->make('Illuminate\Contracts\Events\Dispatcher');
		$this->container = $container;
	}

	/**
	 * Get the class name of the model specific to this repository.
	 *
	 * @return string
	 */
	abstract protected function model();

	/**
	 * Get a model created event instance specific to this repository.
	 *
	 * @param Model $model
	 *
	 * @return \App\Events\Event
	 */
	abstract protected function eventForModelCreated(Model $model);

	/**
	 * Get a mass model created event instance specific to this repository.
	 *
	 * @return \App\Events\Event
	 */
	abstract protected function eventForModelsCreated();

	/**
	 * Get a model updated event instance specific to this repository.
	 *
	 * @param Model $model
	 *
	 * @return \App\Events\Event
	 */
	abstract protected function eventForModelUpdated(Model $model);

	/**
	 * Get a mass model updated event instance specific to this repository.
	 *
	 * @return \App\Events\Event
	 */
	abstract protected function eventForModelsUpdated();

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
		$model = $this->container->make($this->model());
		
		$model->fill($data);

		$model->save();

		$this->cache->tags($this->model->getTable())->flush();

		$this->reset();

		$this->events->fire($this->eventForModelCreated($model));

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createAll(array $data)
	{
		if (empty($data)) return;

		$data = array_map(function($item)
		{
			$item['created_at'] = Carbon::now();
			$item['updated_at'] = Carbon::now();

			return $item;
		}, $data);
		
		$perChunk = 100;

		$chunks = array_chunk($data, $perChunk);

		foreach ($chunks as $chunk)
		{
			$this->query()->insert($chunk);
		}

		$this->cache->tags($this->model->getTable())->flush();

		$this->events->fire($this->eventForModelsCreated());

		$this->reset();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws ModelClassMismatch
	 */
	public function update(Model $model, array $data)
	{
		if (!is_a($model, get_class($this->model))) throw new ModelClassMismatch;

		$model->fill($data);
		
		if ($model->isDirty())
		{
			$model->save(); 

			$this->cache->tags($this->model->getTable())->flush();
			
			$this->events->fire($this->eventForModelUpdated($model));
		}

		$this->reset();

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateAll(array $ids, array $data)
	{
		if (empty($ids)) return;

		$data['updated_at'] = Carbon::now();

		$this->query()->whereIn('id',$ids)->update($data);

		$this->cache->tags($this->model->getTable())->flush();

		$this->events->fire($this->eventForModelsUpdated());

		$this->reset();
	}

	/**
	 * {@inheritdoc}
	 */
	public function incrementAll(array $ids, $column, $amount=1)
	{
		if (empty($ids)) return;

//		$this->query()->whereIn('id',$ids)->update(['updated_at' => Carbon::now(), $column => $this->container->make('db')->raw('`'.$column.'` + 1')]);
		$this->query()->whereIn('id',$ids)->increment($column, $amount,['updated_at' => Carbon::now()]);

		$this->cache->tags($this->model->getTable())->flush();

		$this->reset();
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
	public function count()
	{
		$hash = $this->getQueryHash('count');

		$tags = $this->getCacheTags();

		$results = $this->cache->tags($tags)->rememberForever($hash, function()
		{
		    return $this->query()->count('id');
		});

		$this->reset();

		return $results;
	}

	/**
	 * {@inheritdoc}
	 */
	public function average($column)
	{
		return $this->calculate('avg',$column);
	}

	/**
	 * Perform a calculation query.
	 *
	 * @param string $type
	 * @param string $column
	 *
	 * @return mixed
	 */
	protected function calculate($type, $column)
	{
		$hash = $this->getQueryHash($type, $column);

		$tags = $this->getCacheTags();

		$results = $this->cache->tags($tags)->rememberForever($hash, function() use ($type, $column)
		{
			return call_user_func([$this->query(),$type],$column);
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
	 * @param mixed $data, ...
	 * @return string
	 */
	protected function getQueryHash($data = null)
	{
		$data = ($data) ? func_get_args() : [];

		$hash = $this->query()->getQuery()->toSql() . ' ' . implode(',', $data) . ' ' . implode(',', $this->query()->getBindings());

		$relations = array_keys($this->query()->getEagerLoads());

		// Order doesn't actually matter, but it WOULD change the hash.
		sort($relations);
		
		foreach ($relations as $key)
		{
			$relation = $this->query()->getRelation($key);
			$hash .= $relation->toSql() . ' ' . implode(',', $relation->getBindings());
		}

		return md5($hash);
	}

}