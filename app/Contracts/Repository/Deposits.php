<?php namespace App\Contracts\Repository;

use App\Models\Deposit;

interface Deposits extends RepositoryContract {

    /**
     * @param string $transactionId
     * @param bool $orderAsc if true order ascending, descending otherwise
     * @param bool $withChildren
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByTransactionId($transactionId, $orderAsc=true, $withChildren=false);


    /**
     * @param $transactionId
     * @return Deposit
     */
    public function getStateGivingDeposit($transactionId);
}
