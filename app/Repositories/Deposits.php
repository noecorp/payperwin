<?php namespace App\Repositories;

use App\Contracts\Repository\Deposits as DepositsRepository;
use App\Models\Deposit;
use Illuminate\Cache\NullStore;


class Deposits extends AbstractRepository implements DepositsRepository
{


    public function __construct()
    {
        //disable cache
        parent::__construct(new \Illuminate\Cache\Repository(new NullStore()));
    }

    /**
     * {@inheritdoc}
     *
     * @return Deposit
     */
    protected function model()
    {
        return new Deposit();
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
        return $this->query()->where('processed', true)->where(function ($query) use ($transactionId) {
            $query->where('transaction_id', $transactionId)->orWhere('parent_transaction_id', $transactionId);
        })->orderBy('status_code', 'desc')->orderBy('payment_date', 'desc')->get()->first();
    }
}