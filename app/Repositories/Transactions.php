<?php namespace App\Repositories;

use App\Contracts\Repository\Transactions as TransactionsRepository;
use App\Models\Transaction;

class Transactions extends AbstractRepository implements TransactionsRepository {

	/**
	 * {@inheritdoc}
	 *
	 * @return Transaction
	 */
	protected function model()
	{
		return new Transaction;
	}

}