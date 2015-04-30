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
	 * {@inheritdoc}
	 */
	protected $unique = true;

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
	 * Game api client implementation.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Users repositorory implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Matches repository implementation.
	 *
	 * @var Matches
	 */
	protected $matches;

	/**
	 * Pledge distribution service implementation.
	 *
	 * @var Distribution
	 */
	protected $distribute;

	/**
	 * Cache repository implementation.
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * {@inheritdoc}
	 *
	 * @param int $streamerId
	 * @param int $matchId
	 */
	public function __construct($streamerId, $matchId)
	{
		parent::__construct($streamerId, $matchId);

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
		$this->client = $client;
		$this->users = $users;
		$this->matches = $matches;
		$this->distribute = $distribute;
		$this->cache = $cache;

		$this->start();
	}

	protected function work()
	{
		$originalRate = $rate = 0;

		try
		{
			$originalRate = $rate = $this->cache->rememberForever('api.league.rate', function()
			{
				return 0;
			});

			if ($rate > 9)
			{
				$this->release(12);
			}

			$streamer = $this->users->isStreamer()->find($this->streamerId);

			if (!$streamer)
			{
				$this->delete();

				return;
			}

			$latest = ($this->matchId) ? $this->matches->find($this->matchId) : null;

			$this->cache->increment('api.league.rate');
			$rate++;

			$history = $this->client->matchHistoryForSummonerIdInRegion($streamer->summoner_id,$streamer->region);

			$this->cache->decrement('api.league.rate');
			$rate--;

			$ids = $history->map(function($item)
			{
				return $item->id();
			})->toArray();

			// Check for duplicates.

			$existing = $this->matches->forStreamer($this->streamerId)->havingServerMatchIds($ids)->all();

			$history = $history->filter(function($item) use ($existing)
			{
				$found = false;
				foreach ($existing as $match)
				{
					if ($match->server_match_id == $item->id())
					{
						$found = true;
						break;
					}
				}
				return (!$found);
			});

			foreach ($history as $item)
			{
				$this->matches->create([
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

			// Now distribute pledges for the found matches.
			if (!$history->isEmpty())
			{
				$this->distribute->pledgesFor($this->streamerId);
			}
		}
		catch (\Exception $e)
		{
			$this->delete();

			if ($rate > $originalRate)
			{
				$this->cache->decrement('api.league.rate');
			}
			
			throw $e;
		}

		$this->delete();
	}

}
