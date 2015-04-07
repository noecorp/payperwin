<?php namespace App\Repositories;

use App\Contracts\Repository\Deposits as DepositsRepository;
use App\Models\Deposit;
use App\Events\Repositories\DepositWasCreated;
use App\Events\Repositories\DepositWasUpdated;
use App\Events\Repositories\DepositsWereCreated;
use App\Events\Repositories\DepositsWereUpdated;

class Deposits extends AbstractRepository implements DepositsRepository {

	/**
	 * {@inheritdoc}
	 *
	 * @return Deposit
	 */
	protected function model()
	{
		return new Deposit;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositWasCreated
	 */
	protected function eventForModelCreated($model)
	{
		return new DepositWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositsWereCreated
	 */
	protected function eventForModelsCreated(array $models)
	{
		return new DepositsWereCreated($models);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositWasUpdated
	 */
	protected function eventForModelUpdated($model)
	{
		return new DepositWasUpdated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositsWereUpdated
	 */
	protected function eventForModelsUpdated(array $models)
	{
		return new DepositsWereUpdated($models);
	}

}