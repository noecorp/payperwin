<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Transaction as TransactionGuruInterface;

class Transaction implements TransactionGuruInterface {

	/**
	 * List of all valid Transaction types.
	 *
	 * @var array
	 */
	protected $types = [
		'pledge-taken' => 1,
		'pledge-paid' => 2,
		'funds-deposited' => 3,
		'streamer-paid-out' => 4,
	];

	/**
	 * {@inheritdoc}
	 */
	public function types()
	{
		return array_values($this->types);
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgeTaken()
	{
		return $this->types['pledge-taken'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgePaid()
	{
		return $this->types['pledge-paid'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function fundsDeposited()
	{
		return $this->types['funds-deposited'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function streamerPaidOut()
	{
		return $this->types['streamer-paid-out'];
	}

}
