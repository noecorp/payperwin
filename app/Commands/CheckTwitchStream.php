<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;
use Illuminate\Contracts\Cache\Repository as Cache;
use GuzzleHttp\Client;

class CheckTwitchStream extends Command implements SelfHandling, ShouldBeQueued {

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
	 * Guzzle client implementation.
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
	 * Create a new command instance.
	 *
	 * @param int $streamerId
	 *
	 * @return void
	 */
	public function __construct($streamerId)
	{
		parent::__construct($streamerId);

		$this->streamerId = $streamerId;
	}

	/**
	 * Execute the command.
	 *
	 * @param Client $client
	 * @param Users $users
	 * @param Cache $cache
	 *
	 * @return void
	 */
	public function handle(Client $client, Users $users, Cache $cache)
	{
		$this->client = $client;
		$this->users = $users;
		$this->cache = $cache;

		$this->start();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function work()
	{
		try
		{
			$streamer = $this->users->isStreamer()->find($this->streamerId);

			if (!$streamer)
			{
				$this->delete();

				return;
			}

			$response = $this->client->get('https://api.twitch.tv/kraken/streams/'.$streamer->twitch_username, [
				'timeout' => 10,
				'exceptions' => true,
				'headers' => ['Accept' => 'application/vnd.twitchtv.v3+json']
			]);

			$object = $response->json();

			if ($object['stream'] !== null)
			{
				$this->users->update($streamer,['live'=>1]);
			}
			else
			{
				$this->users->update($streamer,['live'=>0]);	
			}
		}
		catch (\Exception $exception)
		{
			$this->delete();

			throw $exception;
		}

		$this->delete();
	}

}
