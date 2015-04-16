<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Aggregation as AggregationGuruInterface;

class Aggregation implements AggregationGuruInterface {

	const PLEDGE_FROM_USER = 1;
	const PLEDGE_TO_STREAMER = 2;
	const PAID_BY_USER = 3;
	const PAID_TO_STREAMER = 4;

	const DAILY = 1;
	const WEEKLY = 2;
	const MONTHLY = 3;
	const YEARLY = 4;
	const TOTAL = 5;

	/**
	 * {@inheritdoc}
	 */
	public function types()
	{
		return [
			self::DAILY,
			self::WEEKLY,
			self::MONTHLY,
			self::YEARLY,
			self::TOTAL,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgeFromUser()
	{
		return self::PLEDGE_FROM_USER;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgeToStreamer()
	{
		return self::PLEDGE_TO_STREAMER;
	}

	/**
	 * {@inheritdoc}
	 */
	public function paidByUser()
	{
		return self::PAID_BY_USER;
	}

	/**
	 * {@inheritdoc}
	 */
	public function paidToStreamer()
	{
		return self::PAID_TO_STREAMER;
	}

	/**
	 * {@inheritdoc}
	 */
	public function daily()
	{
		return self::DAILY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function weekly()
	{
		return self::WEEKLY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function monthly()
	{
		return self::MONTHLY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function yearly()
	{
		return self::YEARLY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function total()
	{
		return self::TOTAL;
	}

}
