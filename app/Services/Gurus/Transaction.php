<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Transaction as TransactionGuruInterface;

class Transaction implements TransactionGuruInterface {

	const PLEDGE_TAKEN = 1;
	const PLEDGE_PAID = 2;
	const FUNDS_DEPOSITED = 3;
	const STREAMER_PAID_OUT = 4;

	/**
	 * List of all valid Transaction types.
	 *
	 * @var array
	 */
	protected $types = [
		self::PLEDGE_TAKEN,
		self::PLEDGE_PAID,
		self::FUNDS_DEPOSITED,
		self::STREAMER_PAID_OUT,
	];

	/**
	 * {@inheritdoc}
	 */
	public function types()
	{
		return $this->types;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgeTaken()
	{
		return self::PLEDGE_TAKEN;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgePaid()
	{
		return self::PLEDGE_PAID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fundsDeposited()
	{
		return self::FUNDS_DEPOSITED;
	}

	/**
	 * {@inheritdoc}
	 */
	public function streamerPaidOut()
	{
		return self::STREAMER_PAID_OUT;
	}

}
