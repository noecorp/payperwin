<?php namespace App\Contracts\Service\GameApi\League;

interface Client {

	/**
	 * Fetch the summoner data associated with the given
	 * standardized summoner name (lowercase, no spaces).
	 *
	 * @param string $name
	 * @param string $region
	 *
	 * @throws \App\Exceptions\Services\GameApi\PlayerNotFound
	 * @throws \App\Exceptions\Services\GameApi\RateLimitExceeded
	 * @throws \App\Exceptions\Services\GameApi\AccessUnauthorized
	 * @throws \App\Exceptions\Services\GameApi\InternalServerError
	 * @throws \App\Exceptions\Services\GameApi\ServiceUnavailable
	 * @throws \App\Exceptions\Services\GameApi\UnknownError
	 * @throws \App\Exceptions\Services\GameApi\BadRequest
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
	 * @throws \App\Exceptions\Services\GameApi\MatchesNotFound
	 * @throws \App\Exceptions\Services\GameApi\RateLimitExceeded
	 * @throws \App\Exceptions\Services\GameApi\AccessUnauthorized
	 * @throws \App\Exceptions\Services\GameApi\InternalServerError
	 * @throws \App\Exceptions\Services\GameApi\ServiceUnavailable
	 * @throws \App\Exceptions\Services\GameApi\UnknownError
	 * @throws \App\Exceptions\Services\GameApi\BadRequest
	 *
	 * @return array
	 */
	public function matchHistoryForSummonerIdInRegion($summonerId, $region);

}