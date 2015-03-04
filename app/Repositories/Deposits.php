<?php namespace App\Repositories;

use App\Contracts\Repository\Deposits as DepositsRepository;
use App\Models\Deposit;

class Deposits implements DepositsRepository {
	
	public function create(array $data)
	{
		return Deposit::create($data);
	}

}