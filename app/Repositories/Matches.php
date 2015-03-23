<?php namespace App\Repositories;

use App\Contracts\Repository\Matches as MatchesRepository;
use App\Models\Match;

class Matches extends AbstractRepository implements MatchesRepository {

	/**
	 * {@inheritdoc}
	 *
	 * @return Match
	 */
	protected function model()
	{
		return new Match;
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