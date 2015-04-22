<?php namespace App\Repositories;

use App\Repositories\AbstractRepository;
use App\Contracts\Repository\Aggregations as AggregationsRepository;
use App\Models\Aggregation;
use App\Models\Model;
use App\Events\Repositories\AggregationWasCreated;
use App\Events\Repositories\AggregationWasUpdated;
use App\Events\Repositories\AggregationsWereCreated;
use App\Events\Repositories\AggregationsWereUpdated;
use App\Contracts\Service\Gurus\Aggregation as AggregationGuruInterface;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Container\Container;

class Aggregations extends AbstractRepository implements AggregationsRepository {

	/**
	 * Aggregation Guru implementation.
	 *
	 * @var AggregationGuruInterface
	 */
	protected $guru;

	/**
	 * 'Since' date of the aggregations.
	 *
	 * Used to construct range queries.
	 *
	 * @var Carbon
	 */
	protected $since = null;

	/**
	 * Type of aggregation.
	 *
	 * Used to construct range queries.
	 *
	 * @var int
	 */
	protected $type = null;

	/**
	 * {@inheritdoc}
	 *
	 * @param 
	 */
	public function __construct(Container $container, AggregationGuruInterface $guru)
	{
		parent::__construct($container);

		$this->guru = $guru;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function model()
	{
		return Aggregation::class;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchWasCreated
	 */
	protected function eventForModelCreated(Model $model)
	{
		return new AggregationWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchesWereCreated
	 */
	protected function eventForModelsCreated()
	{
		return new AggregationsWereCreated();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchWasUpdated
	 */
	protected function eventForModelUpdated(Model $model, array $changed)
	{
		return new AggregationWasUpdated($model, $changed);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchesWereUpdated
	 */
	protected function eventForModelsUpdated()
	{
		return new AggregationsWereUpdated();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function reset()
	{
		parent::reset();

		$this->type = null;
		$this->since = null;
	}

	/**
	 * Add date constraints to the query.
	 *
	 * @return void
	 */
	protected function applyRangeConstraints()
	{
		if ($this->since && $this->type)
		{
			switch ($this->type) {
				case $this->guru->daily():
					$this->query()->where('day','>=',$this->since->day);
				case $this->guru->monthly():
					$this->query()->where('month','>=',$this->since->month);
				case $this->guru->yearly():
					$this->query()->where('year','>=',$this->since->format('y'));
					break;
				case $this->guru->weekly():
					$this->query()->where('week','>=',$this->since->weekOfYear)->where('year','>=',$this->since->format('y'));
					break;
				default:
					break;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function find($id = null)
	{
		$this->applyRangeConstraints();

		return parent::find($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function all()
	{
		$this->applyRangeConstraints();

		return parent::all();
	}

	/**
	 * {@inheritdoc}
	 */
	public function since(DateTime $date)
	{
		if (!($date instanceof Carbon))
		{
			$date = new Carbon($date);

			$date->setTimezone('UTC');
		}

		$this->since = $date;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDaily(DateTime $date = null)
	{
		$type = $this->guru->daily();

		if ($date)
		{
			if (!($date instanceof Carbon))
			{
				$date = new Carbon($date);

				$date->setTimezone('UTC');

				$this->query()->where('day', $date->day)->where('week', 0)->where('month', $date->month)->where('year', (int)$date->format('y'));
			}
		}
		else
		{
			$this->type = $type;
		}

		$this->query()->where('type',$type);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isWeekly(DateTime $date)
	{
		if (!($date instanceof Carbon)) $date = new Carbon($date);

		$date->setTimezone('UTC');

		$this->query()->whereType($this->guru->weekly())->where('day', 0)->where('week', $date->weekOfYear)->where('month', 0)->where('year', (int)$date->format('y'));

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMonthly(DateTime $date)
	{
		if (!($date instanceof Carbon)) $date = new Carbon($date);

		$date->setTimezone('UTC');

		$this->query()->whereType($this->guru->monthly())->where('day', 0)->where('week', 0)->where('month', $date->month)->where('year', (int)$date->format('y'));

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isYearly(DateTime $date)
	{
		if (!($date instanceof Carbon)) $date = new Carbon($date);

		$date->setTimezone('UTC');

		$this->query()->whereType($this->guru->yearly())->where('day', 0)->where('week', 0)->where('month', 0)->where('year', (int)$date->format('y'));

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isTotal()
	{
		$this->query()->whereType($this->guru->total())->where('day', 0)->where('week', 0)->where('month', 0)->where('year', 0);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function forReason($reason)
	{
		$this->query()->whereReason($reason);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function forUser($id)
	{
		$this->query()->whereUserId($id);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function forDate(DateTime $date)
	{
		if (!$date instanceof Carbon) $date = new Carbon($date);

		$date->setTimezone('UTC');

		$this->query()->where(function($query) use ($date)
		{
			$query->where(function($query) use ($date)
			{
				$query->where('day',$date->day)
					->where('week',0)
					->where('month',$date->month)
					->where('year',(int)$date->format('y'));
			})
				->orWhere(function($query) use ($date)
			{
				$query->where('day',0)
					->where('week',$date->weekOfYear)
					->where('month',0)
					->where('year',(int)$date->format('y'));
			})
				->orWhere(function($query) use ($date)
			{
				$query->where('day',0)
					->where('week',0)
					->where('month',$date->month)
					->where('year',(int)$date->format('y'));
			})
				->orWhere(function($query) use ($date)
			{
				$query->where('day',0)
					->where('week',0)
					->where('month',0)
					->where('year',(int)$date->format('y'));
			})
				->orWhere(function($query) use ($date)
			{
				$query->where('day',0)
					->where('week',0)
					->where('month',0)
					->where('year',0);
			});
		});

		return $this;
	}

}
