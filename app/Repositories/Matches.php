<?php namespace App\Repositories;

use App\Contracts\Repository\Matches as MatchesRepository;
use App\Models\Match;
use App\Models\Model;
use App\Events\Repositories\MatchWasCreated;
use App\Events\Repositories\MatchWasUpdated;
use App\Events\Repositories\MatchesWereCreated;
use App\Events\Repositories\MatchesWereUpdated;

class Matches extends AbstractRepository implements MatchesRepository {

	/**
	 * {@inheritdoc}
	 */
	protected function model()
	{
		return Match::class;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchWasCreated
	 */
	protected function eventForModelCreated(Model $model)
	{
		return new MatchWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchesWereCreated
	 */
	protected function eventForModelsCreated(array $models)
	{
		return new MatchesWereCreated($models);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchWasUpdated
	 */
	protected function eventForModelUpdated(ModelModel$model)
	{
		return new MatchWasUpdated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchesWereUpdated
	 */
	protected function eventForModelsUpdated(array $models)
	{
		return new MatchesWereUpdated($models);
	}

	/**
	 * {@inheritdoc}
	 */
	public function forStreamer($id)
	{
		$this->query()->whereUserId($id);

		return $this;
	}

	public function orderingByMatchDate($latest = true)
	{
		if ($latest)
		{
			$this->query()->orderBy('match_date','desc');
		}
		else
		{
			$this->query()->orderBy('match_date','asc');	
		}

		return $this;
	}

	public function isUnsettled()
	{
		$this->query()->whereSettled(0);

		return $this;
	}
}