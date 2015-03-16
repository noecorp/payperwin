<?php namespace App\Services\Api;

use App\Contracts\Service\Api\League as LeagueInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\TooManyRedirectsException;

class League implements LeagueInterface {

	protected $client;

	protected $baseUrl;

	protected $apiKey;

	public function __construct(Client $client)
	{
		$this->client = $client;

		$this->baseUrl = 'api.pvp.net/api/lol/';

		$this->apiKey = env('RIOT_KEY');
	}

	/**
	 * {@inheritdoc}
	 */
	public function summonerForNameInRegion($name, $region)
	{
		try
		{
			$response = $this->client->get($this->url('v1.4/summoner/by-name/'.$name, $region), [
				'timeout' => $this->timeout(),
				'exceptions' => true
			]);

			// echo $response->getStatusCode();
			// echo $response->getBody();
			$object = $response->json();
			return $object[$name];
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
			$response = $this->client->get($this->url('v2.2/matchhistory/'.$summonerId, $region), [
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