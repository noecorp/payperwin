<?php namespace App\Repositories;

use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Models\Pledge;
use Carbon\Carbon;
use App\Events\Repositories\PledgeWasCreated;
use App\Events\Repositories\PledgeWasUpdated;
use App\Events\Repositories\PledgesWereCreated;
use App\Events\Repositories\PledgesWereUpdated;

class Pledges extends AbstractRepository implements PledgesRepository {

	/**
	 * {@inheritdoc}
	 *
	 * @return Pledge
	 */
	protected function model()
	{
		return new Pledge;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgeWasCreated
	 */
	protected function eventForModelCreated($model)
	{
		return new PledgeWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgesWereCreated
	 */
	protected function eventForModelsCreated(array $models)
	{
		return new PledgesWereCreated($models);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgeWasUpdated
	 */
	protected function eventForModelUpdated($model)
	{
		return new PledgeWasUpdated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return PledgesWereUpdated
	 */
	protected function eventForModelsUpdated(array $models)
	{
		return new PledgesWereUpdated($models);
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

}