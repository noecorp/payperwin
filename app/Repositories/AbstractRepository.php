<?php namespace App\Repositories;

use App\Contracts\Repository\RepositoryContract;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Container\Container;
use Carbon\Carbon;
use App\Models\Model;
use App\Exceptions\Repositories\ModelClassMismatch;
use App\Exceptions\Repositories\ModelDoesntExist;
use DateTime;

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
	 * Switch controlling whether or not model events will be generated.
	 *
	 * @var boolean
	 */
	protected $sendEvents = true;

	/**
	 * Switch controlling whether or not cached data will be used when available.
	 *
	 * @var boolean
	 */
	protected $useCache = true;

	/**
	 * List of models already requested.
	 *
	 * Makes it easier to avoid requests for objects that already exist in memory.
	 *
	 * @var array
	 */
	protected $models = [];

	/**
	 * Create a new instance of the repository.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->model = $container->make($this->model());
		$this->cache = $container->make(Cache::class);
		$this->events = $container->make(Events::class);
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
	 * @return \App\Events\ModelEvent
	 */
	abstract protected function eventForModelCreated(Model $model);

	/**
	 * Get a mass model created event instance specific to this repository.
	 *
	 * @return \App\Events\ModelsEvent
	 */
	abstract protected function eventForModelsCreated();

	/**
	 * Get a model updated event instance specific to this repository.
	 *
	 * @param Model $model
	 * @param array $changed
	 *
	 * @return \App\Events\ModelUpdated
	 */
	abstract protected function eventForModelUpdated(Model $model, array $changed);

	/**
	 * Get a mass model updated event instance specific to this repository.
	 *
	 * @return \App\Events\ModelsEvent
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

		$this->sendEvents = true;

		$this->useCache = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function quietly()
	{
		$this->sendEvents = false;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function freshly()
	{
		$this->useCache = false;

		return $this;
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

		$sendEvents = $this->sendEvents;

		$this->reset();

		if ($sendEvents)
		{
			$this->events->fire($this->eventForModelCreated($model));
		}

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
			// Rather than pushing attributes directly into DB, we'll fill a blank model
			// first and get the set attributes back to make sure any model-specific logic
			// is triggered. Attributes may be changed or omitted altogether.
			$model = $this->container->make($this->model());

			$model->fill($item);

			$final = $model->getAttributes();
			$final['created_at'] = Carbon::now();
			$final['updated_at'] = Carbon::now();

			return $final;
		}, $data);
		
		$perChunk = 100;

		$chunks = array_chunk($data, $perChunk);

		foreach ($chunks as $chunk)
		{
			$this->query()->insert($chunk);
		}

		$this->cache->tags($this->model->getTable())->flush();

		$sendEvents = $this->sendEvents;

		$this->reset();

		if ($sendEvents)
		{
			$this->events->fire($this->eventForModelsCreated());
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws ModelClassMismatch
	 * @throws ModelDoesntExist
	 */
	public function update(Model $model, array $data)
	{
		if (!is_a($model, get_class($this->model))) throw new ModelClassMismatch;

		if (!$model->exists) throw new ModelDoesntExist;

		$model->fill($data);

		if ($model->isDirty())
		{
			$dirty = array_keys($model->getDirty());

			foreach ($dirty as $column)
			{
				// Make sure the model actually has the attribute originally
				if (isset($model->getOriginal()[$column]))
				{
					$changed[$column] = $model->getOriginal()[$column];
				}
			}
			
			$changed['updated_at'] = (string)$model->updated_at;

			$model->save(); 

			$this->cache->tags($this->model->getTable())->flush();
			
			$sendEvents = $this->sendEvents;

			$this->reset();

			if ($sendEvents)
			{
				$this->events->fire($this->eventForModelUpdated($model, $changed));
			}
		}
		else
		{
			$this->reset();
		}

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateAll(array $ids, array $data)
	{
		if (empty($ids) || empty($data)) return;

		$data['updated_at'] = Carbon::now();

		$this->query()->whereIn('id',$ids)->update($data);

		$this->cache->tags($this->model->getTable())->flush();

		$sendEvents = $this->sendEvents;

		$this->reset();

		if ($sendEvents)
		{
			$this->events->fire($this->eventForModelsUpdated());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function increment(Model $model, $column, $amount = 1.0)
	{
		return $this->update($model, [$column => $model->$column + $amount]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function incrementAll(array $ids, $column, $amount=1)
	{
		if (empty($ids)) return;

		$this->query()->whereIn('id', $ids)->increment($column, $amount, ['updated_at' => Carbon::now()]);

		$this->cache->tags($this->model->getTable())->flush();

		$sendEvents = $this->sendEvents;

		$this->reset();

		if ($sendEvents)
		{
			$this->events->fire($this->eventForModelsUpdated());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($id = null)
	{
		if ($id)
		{
			// If the model instance is stored in memory and no eager loads are being called,
			// just return the available object since any updates will have kept it up to 
			// date.
			if (isset($this->models[$this->model->getTable()][$id]) && empty($this->query()->getEagerLoads()))
			{
				$this->reset();

				return $this->models[$this->model->getTable()][$id];
			}

			$this->query()->where($this->model->getTable().'.id', $id);
		}
		else
		{
			$this->query()->limit(1);
		}

		$closure = function()
		{
			return $this->query()->first();
		};

		if ($this->useCache)
		{
			$hash = $this->getQueryHash();

			$tags = $this->getCacheTags();

			$model = $this->cache->tags($tags)->rememberForever($hash, $closure);
		}
		else
		{
			$model = $closure();
		}

		// Set the model instance in memory for easy access later, but only if not eager
		// loads were called when querying.
		if ($model && empty($this->query()->getEagerLoads()))
		{
			$this->models[$this->model->getTable()][$model->id] = $model;
		}

		$this->reset();

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all()
	{
		$closure = function()
		{
			return $this->query()->get();
		};

		if ($this->useCache)
		{
			$hash = $this->getQueryHash();

			$tags = $this->getCacheTags();

			$results = $this->cache->tags($tags)->rememberForever($hash, $closure);
		}
		else
		{
			$results = $closure();
		}

		$this->reset();

		return $results;
	}

	/**
	 * {@inheritdoc}
	 */
	public function count($column = 'id')
	{
		$closure = function() use ($column)
		{
			return $this->query()->count($column);
		};

		if ($this->useCache)
		{
			$hash = $this->getQueryHash('count', $column);

			$tags = $this->getCacheTags();

			$results = $this->cache->tags($tags)->rememberForever($hash, $closure);
		}
		else
		{
			$results = $closure();
		}

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
		$closure = function() use ($type, $column)
		{
			return call_user_func([$this->query(),$type],$column);
		};

		if ($this->useCache)
		{
			$hash = $this->getQueryHash($type, $column);

			$tags = $this->getCacheTags();

			$results = $this->cache->tags($tags)->rememberForever($hash, $closure);
		}
		else
		{
			$results = $closure();
		}

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
	public function after(DateTime $date)
	{
		$this->query()->where('created_at','>',$date);
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function before(DateTime $date)
	{
		$this->query()->where('created_at','<',$date);
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function limit($take, $page = null)
	{
		$this->query()->limit($take);
		
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

		$tags = ['models', $this->model->getTable()];

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
