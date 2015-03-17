<?php namespace App\Repositories;

use App\Contracts\Repository\Deposits as DepositsRepository;
use App\Models\Deposit;

class Deposits extends AbstractRepository implements DepositsRepository {

	/**
	 * {@inheritdoc}
	 *
	 * @return Pledge
	 */
	protected function model()
	{
		return new Deposit;
	}

}