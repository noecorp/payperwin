<?php namespace App\Contracts\Service\Api;

interface League {

	/**
	 * Fetch the summoner data associated with the given
	 * standardized summoner name (lowercase, no spaces).
	 *
	 * @param string $name
	 * @param string $region
	 *
	 * @throws \App\Exceptions\Api\League\...
	 *
	 * @return array
	 */
	public function summonerForNameInRegion($name, $region);

	/**
	 * Fetch latest games for the given player and region.
	 *
	 * @param string $summoner
	 * @param string $region
	 *
	 * @throws \App\Exceptions\Api\League\...
	 *
	 * @return array
	 */
	public function matchHistoryForSummonerIdInRegion($summonerId, $region);

}