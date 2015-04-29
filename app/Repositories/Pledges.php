<?php namespace App\Repositories;

use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Models\Pledge;
use App\Models\Model;
use Carbon\Carbon;
use App\Events\Repositories\PledgeWasCreated;
use App\Events\Repositories\PledgeWasUpdated;
use App\Events\Repositories\PledgesWereCreated;
use App\Events\Repositories\PledgesWereUpdated;
use Illuminate\Database\Query\Expression;

class Pledges extends AbstractRepository implements PledgesRepository {

	/**
	 * {@inheritdoc}
	 */
	protected function model()
	{
		return Pledge::class;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgeWasCreated
	 */
	protected function eventForModelCreated(Model $model)
	{
		return new PledgeWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgesWereCreated
	 */
	protected function eventForModelsCreated()
	{
		return new PledgesWereCreated();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgeWasUpdated
	 */
	protected function eventForModelUpdated(Model $model, array $changed)
	{
		return new PledgeWasUpdated($model, $changed);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgesWereUpdated
	 */
	protected function eventForModelsUpdated()
	{
		return new PledgesWereUpdated();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(array $data)
	{
		$data['end_date'] = (isset($data['end_date']) && $data['end_date']) ? Carbon::createFromFormat('d-m-Y', $data['end_date']) : null;

		return parent::create($data);
	}

	public function withStreamer()
	{
		$this->query()->with('streamer');

		return $this;
	}

	public function withOwner()
	{
		$this->query()->with('owner');

		return $this;
	}

	public function forStreamer($streamerId)
	{
		$this->query()->whereStreamerId($streamerId);

		return $this;
	}

	public function fromUser($userId)
	{
		$this->query()->whereUserId($userId);
		
		return $this;
	}

	public function isRunning()
	{
		$this->query()->whereRunning(1);

		return $this;
	}

	public function orderingByAmount($highest = true)
	{
		if ($highest)
		{
			$this->query()->orderBy('amount','desc');
		}
		else
		{
			$this->query()->orderBy('amount','asc');	
		}

		return $this;
	}

	public function mostSpent()
	{
		$this->query()->select('*', new Expression('sum(`amount` * `times_donated`) as spent'))->groupBy('user_id');

		return $this;
	}

	public function countPledgers()
	{
		$this->query()->distinct();

		return $this->count('user_id');
	}

	public function donated()
	{
		$this->query()->where('times_donated', '>', 0);

		return $this;
	}

	public function today()
	{
		$now = Carbon::now();

		$this->query()->whereDay('created_at', '=', $now->format('d'))
			->whereMonth('created_at', '=', $now->format('m'))
			->whereYear('created_at', '=', $now->format('Y'));

		return $this;
	}

	public function thisMonth()
	{
		$now = Carbon::now();

		$this->query()->whereMonth('created_at', '=', $now->format('m'))
			->whereYear('created_at', '=', $now->format('Y'));

		return $this;
	}

	public function thisWeek()
	{
		$now = Carbon::now();

		$this->query()->whereDay('created_at', '>=', $now->startOfWeek()->format('d'))
			->whereDay('created_at', '<=', $now->endOfWeek()->format('d'))
			->whereMonth('created_at', '=', $now->format('m'))
			->whereYear('created_at', '=', $now->format('Y'));

		return $this;
	}

}
