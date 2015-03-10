<?php namespace App\Repositories;

use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Models\Pledge;

class Pledges extends AbstractRepository implements PledgesRepository {

	/**
	 * {@inheritdoc}
	 *
	 * @param \App\Models\Pledge $pledge
	 */
	public function __construct(Pledge $pledge)
	{
		parent::__construct($pledge);
	}

	public function latestWithUsersAndStreamers($take, $page = null)
	{
		$query = $this->newQuery()->with('owner','streamer');

		$this->addLimits($query,$take,$page);

		return $query->get();
	}

	public function latestForStreamerWithUsers($streamerId, $take, $page = null)
	{
		$query = $this->newQuery()->with('owner')->whereStreamerId($streamerId);

		$this->addLimits($query,$take,$page);

		return $query->get();
	}

	public function latestForUserWithStreamers($userId, $take, $page = null)
	{
		$query = $this->newQuery()->with('streamer')->whereUserId($userId);

		$this->addLimits($query,$take,$page);

		return $query->get();
	}

	public function havingIdWithStreamer($id)
	{
		return $this->newQuery()->with('streamer')->find($id);
	}

}