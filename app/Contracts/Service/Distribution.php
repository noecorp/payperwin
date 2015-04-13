<?php namespace App\Contracts\Service;

interface Distribution {

	/**
	 * Distribute all relevant, active pledges for a streamer's unsettled matches.
	 *
	 * @param int $streamerId
	 *
	 * @return void
	 */
	public function pledgesFor($streamerId);

}
