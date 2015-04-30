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
	protected function eventForModelsCreated()
	{
		return new MatchesWereCreated();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchWasUpdated
	 */
	protected function eventForModelUpdated(Model $model, array $changed)
	{
		return new MatchWasUpdated($model, $changed);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return MatchesWereUpdated
	 */
	protected function eventForModelsUpdated()
	{
		return new MatchesWereUpdated();
	}

	/**
	 * {@inheritdoc}
	 */
	public function forStreamer($id)
	{
		$this->query()->whereUserId($id);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function havingServerMatchIds(array $matchIds)
	{
		$this->query()->whereIn('server_match_id', $matchIds);

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
