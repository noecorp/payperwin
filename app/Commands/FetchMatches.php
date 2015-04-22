<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;
use App\Contracts\Repository\Matches;
use App\Contracts\Service\GameApi\League\Client;
use App\Contracts\Service\Distribution;
use Illuminate\Contracts\Cache\Repository as Cache;

use Carbon\Carbon;
use Exception;

class FetchMatches extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * Streamer identifier for whom to fetch latest matches.
	 *
	 * @var int
	 */
	protected $streamerId;

	/**
	 * Match identifier for the latest match to check against.
	 *
	 * @var int|null
	 */
	protected $matchId;

	/**
	 * Create a new command instance.
	 *
	 * @param int $streamerId
	 * @param int $matchId
	 *
	 * @return void
	 */
	public function __construct($streamerId, $matchId)
	{
		$this->streamerId = $streamerId;
		$this->matchId = $matchId;
	}

	/**
	 * Execute the command.
	 *
	 * @param Client $client
	 * @param Users $users
	 * @param Matches $matches
	 * @param Distribution $distribute
	 * @param Cache $cache
	 *
	 * @return void
	 */
	public function handle(Client $client, Users $users, Matches $matches, Distribution $distribute, Cache $cache)
	{
		try
		{
			$rate = $cache->rememberForever('api.league.rate', function()
			{
				return 0;
			});

			if ($rate > 9)
			{
				$this->release(12);
			}

			$streamer = $users->isStreamer()->find($this->streamerId);

			$latest = ($this->matchId) ? $matches->find($this->matchId) : null;

			$cache->increment('api.league.rate');

			$history = $client->matchHistoryForSummonerIdInRegion($streamer->summoner_id,$streamer->region)->filter(function($item) use ($latest) {
				return (!$latest || ($item->id() != $latest->match_server_id && $item->timestamp() > $latest->match_date->timestamp));
			});

			$cache->decrement('api.league.rate');

			foreach ($history as $item)
			{
				$matches->create([
					'user_id' => $streamer->id,
					'server_match_id' => $item->id(),
					'win' => $item->win(),
					'champion' => $item->champion(),
					'kills' => $item->kills(),
					'assists' => $item->assists(),
					'deaths' => $item->deaths(),
					'match_date' => Carbon::createFromTimestamp($item->timestamp()),
				]);
			}

			$distribute->pledgesFor($this->streamerId);
		}
		catch (Exception $exception)
		{
			$this->delete();

			throw $exception;
		}

		$this->delete();
	}

}
