<?php namespace App\Repositories;

use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Models\Pledge;

class Pledges implements PledgesRepository {
	
	public function create(array $data)
	{
		return Pledge::create($data);
	}

}