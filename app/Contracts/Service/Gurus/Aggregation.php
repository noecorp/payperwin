<?php namespace App\Contracts\Service\Gurus;

interface Aggregation {

	/**
	 * Return list of Aggregation types.
	 *
	 * @return array
	 */
	public function types();

	/**
	 * Return the Aggregation reason for a pledge from a user.
	 *
	 * @return int
	 */
	public function pledgeFromUser();

	/**
	 * Return the Aggregation reason for a pledge to a streamer.
	 *
	 * @return int
	 */
	public function pledgeToStreamer();

	/**
	 * Return the Aggregation reason for amount paid by a user.
	 *
	 * @return int
	 */
	public function paidByUser();

	/**
	 * Return the Aggregation reason for amount paid to a streamer.
	 *
	 * @return int
	 */
	public function paidToStreamer();

	/**
	 * Return the Aggregation daily type.
	 *
	 * @return int
	 */
	public function daily();

	/**
	 * Return the Aggregation weekly type.
	 *
	 * @return int
	 */
	public function weekly();

	/**
	 * Return the Aggregation monthly type.
	 *
	 * @return int
	 */
	public function monthly();

	/**
	 * Return the Aggregation yearly type.
	 *
	 * @return int
	 */
	public function yearly();

	/**
	 * Return the Aggregation total type.
	 *
	 * @return int
	 */
	public function total();

}
