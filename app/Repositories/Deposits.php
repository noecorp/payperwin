<?php namespace App\Repositories;

use App\Contracts\Repository\Deposits as DepositsRepository;
use App\Models\Deposit;
use App\Models\Model;
use Illuminate\Cache\NullStore;
use Illuminate\Contracts\Container\Container;
use App\Events\Repositories\DepositWasCreated;
use App\Events\Repositories\DepositWasUpdated;
use App\Events\Repositories\DepositsWereCreated;
use App\Events\Repositories\DepositsWereUpdated;

class Deposits extends AbstractRepository implements DepositsRepository
{

    public function __construct(Container $container)
    {
        parent::__construct($container);

        //disable cache
        $this->cache = new \Illuminate\Cache\Repository(new NullStore());
    }

    /**
     * {@inheritdoc}
     */
    protected function model()
    {
        return Deposit::class;
    }

    /**
     * Finds deposits based on transaction id, optionally it can also return deposits that belong to the deposit
     * identified by the given transaction id
     *      *
     * @param string $transactionId
     * @param bool $order sort ascending if true, descending otherwise (sorted by payment date)
     * @param bool $withChildrenTransactions if true also returns related deposits
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByTransactionId($transactionId, $order = true, $withChildrenTransactions = false)
    {
        //no cache here
        if ($withChildrenTransactions) {
            $result = $this->query()->where('transaction_id', $transactionId)->orWhere('parent_transaction_id', $transactionId)->orderBy('payment_date', $order ? 'asc' : 'desc')->get();
        } else {
            $result = $this->query()->where('transaction_id', $transactionId)->orderBy('status_code', 'desc')->orderBy('payment_date', $order ? 'asc' : 'desc')->get();
        }

        $this->reset();
        return $result;
    }

    /**
     * Returns the deposit that defines the current state of a transaction (completed, refunded, etc.)
     *
     * @param $transactionId
     * @return Deposit
     */
    public function getStateGivingDeposit($transactionId)
    {
        $result = $this->query()->where('processed', true)->where(function ($query) use ($transactionId) {
            $query->where('transaction_id', $transactionId)->orWhere('parent_transaction_id', $transactionId);
        })->orderBy('status_code', 'desc')->orderBy('payment_date', 'desc')->get()->first();

        $this->reset();

        return $result;
    }

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositWasCreated
	 */
	protected function eventForModelCreated(Model $model)
	{
		return new DepositWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositsWereCreated
	 */
	protected function eventForModelsCreated()
	{
		return new DepositsWereCreated();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositWasUpdated
	 */
	protected function eventForModelUpdated(Model $model, array $changed)
	{
		return new DepositWasUpdated($model, $changed);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return DepositsWereUpdated
	 */
	protected function eventForModelsUpdated()
	{
		return new DepositsWereUpdated();
	}
}
