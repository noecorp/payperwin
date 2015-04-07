<?php namespace App\Repositories;

use App\Contracts\Repository\Transactions as TransactionsRepository;
use App\Models\Transaction;
use App\Models\Model;
use App\Events\Repositories\TransactionWasCreated;
use App\Events\Repositories\TransactionWasUpdated;
use App\Events\Repositories\TransactionsWereCreated;
use App\Events\Repositories\TransactionsWereUpdated;

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

	/**
	 * {@inheritdoc}
	 *
	 * @return TransactionWasCreated
	 */
	protected function eventForModelCreated(Model $model)
	{
		return new TransactionWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return TransactionsWereCreated
	 */
	protected function eventForModelsCreated(array $models)
	{
		return new TransactionsWereCreated($models);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return TransactionWasUpdated
	 */
	protected function eventForModelUpdated(Model $model)
	{
		return new TransactionWasUpdated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return TransactionsWereUpdated
	 */
	protected function eventForModelsUpdated(array $models)
	{
		return new TransactionsWereUpdated($models);
	}

}