<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;

use GuzzleHttp\Client;

class CheckTwitchStream extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * Streamer identifier for whom to fetch latest matches.
	 *
	 * @var int
	 */
	protected $streamerId;

	/**
	 * Create a new command instance.
	 *
	 * @param int $streamerId
	 *
	 * @return void
	 */
	public function __construct($streamerId)
	{
		$this->streamerId = $streamerId;
	}

	/**
	 * Execute the command.
	 *
	 * @param Client $client
	 * @param Users $users
	 * @param Matches $matches
	 * @param Distribution $distribute
	 *
	 * @return void
	 */
	public function handle(Client $client, Users $users)
	{
		$streamer = $users->isStreamer()->find($this->streamerId);

		try
		{
			$response = $client->get('https://api.twitch.tv/kraken/streams/'.$streamer->twitch_username, [
				'timeout' => 10,
				'exceptions' => true,
				'headers' => ['Accept' => 'application/vnd.twitchtv.v3+json']
			]);

			$object = $response->json();

			if ($object['stream'] !== null)
			{
				$users->update($streamer,['live'=>1]);
			}
			else
			{
				$users->update($streamer,['live'=>0]);	
			}
		}
		catch (Exception $exception)
		{
			$this->delete();

			throw $exception;
		}

		$this->delete();
	}

}
