<?php namespace App\Contracts\Service\Gurus;

interface Transaction {

	/**
	 * Returns list of Pledge types.
	 *
	 * @return array
	 */
	public function types();

	/**
	 * Returns the Transaction type associated with pledge amount being subtracted from pledger funds.
	 *
	 * @return int
	 */
	public function pledgeTaken();

	/**
	 * Returns the Transaction type associated with pledge amount being added to streamer earnings.
	 *
	 * @return int
	 */
	public function pledgePaid();

	/**
	 * Returns the Transaction type associated with user making a deposit.
	 *
	 * @return int
	 */
	public function fundsDeposited();

	/**
	 * Returns the Transaction type associated with streamer being paid.
	 *
	 * @return int
	 */
	public function streamerPaidOut();

}
