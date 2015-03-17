<?php namespace App\Repositories;

use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Models\Pledge;

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

	public function withStreamer()
	{
		$this->query()->with('streamer');

		return $this;
	}

	public function withUser()
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

}