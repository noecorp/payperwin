<?php namespace App\Services\GameApi\League;

use App\Contracts\Service\GameApi\League\Client as ClientInterface;

use App\Contracts\Service\GameApi\Player;

use GuzzleHttp\Client as Guzzle;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\TooManyRedirectsException;

class Client implements ClientInterface {

	protected $guzzle;

	protected $baseUrl;

	protected $apiKey;

	protected $player;

	public function __construct(Guzzle $guzzle, Player $player)
	{
		$this->guzzle = $guzzle;

		$this->baseUrl = 'api.pvp.net/api/lol/';

		$this->apiKey = env('RIOT_KEY');

		$this->player = $player;
	}

	/**
	 * {@inheritdoc}
	 */
	public function summonerForNameInRegion($name, $region)
	{
		try
		{
			$response = $this->guzzle->get($this->url('v1.4/summoner/by-name/'.$name, $region), [
				'timeout' => $this->timeout(),
				'exceptions' => true
			]);

			$object = $response->json();

			return $this->player->create($object[$name]['id'],$object[$name]['name']);

			// echo $response->getStatusCode();
			// echo $response->getBody();
		}
		catch (\Exception $e)
		{
			return $this->handle($e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function matchHistoryForSummonerIdInRegion($summonerId, $region)
	{
		try
		{
			$response = $this->guzzle->get($this->url('v2.2/matchhistory/'.$summonerId, $region), [
				'timeout' => $this->timeout(),
				'exceptions' => true
			]);

			// echo $response->getStatusCode();
			// echo $response->getBody();
			$object = $response->json();
			return $object['matches'];
		}
		catch (\Exception $e)
		{
			return $this->handle($e);
		}
	}

	/**
	 * Construct the full request url.
	 *
	 * @param string $uri
	 * @param string $region
	 *
	 * @return string
	 */
	protected function url($uri, $region)
	{
		return 'https://' . $region . '.' . $this->baseUrl . $region . '/' .  $uri . '?api_key=' . $this->apiKey;
	}

	protected function timeout()
	{
		return 10;
	}

	protected function handle(\Exception $e)
	{
		if ($e instanceof ClientException)
		{
			//throw App\Exceptions\Api\League\...
			
			echo $e->getRequest();
			if ($e->hasResponse()) {
		        echo $e->getResponse();
		    }
			// handle
		}
		else if ($e instanceof ServerException)
		{
			//
		}
		else if ($e instanceof TooManyRedirectsException)
		{
			//
		}
		else if ($e instanceof ParseException)
		{
			//
		}
		else
		{

		}
	}

}