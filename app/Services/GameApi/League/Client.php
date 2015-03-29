<?php namespace App\Services\GameApi\League;

use App\Contracts\Service\GameApi\League\Client as ClientInterface;

use App\Contracts\Service\GameApi\Player;
use App\Contracts\Service\GameApi\League\Match;

use GuzzleHttp\Client as Guzzle;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\TooManyRedirectsException;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Client implements ClientInterface {

	protected $guzzle;

	protected $baseUrl;

	protected $apiKey;

	protected $player;
	protected $match;

	public function __construct(Guzzle $guzzle, Player $player, Match $match)
	{
		$this->guzzle = $guzzle;

		$this->baseUrl = 'api.pvp.net/api/lol/';

		$this->apiKey = env('RIOT_KEY');

		$this->player = $player;
		$this->match = $match;
	}

	/**
	 * {@inheritdoc}
	 */
	public function summonerForNameInRegion($name, $region)
	{
		$name = strtolower(str_replace(' ', '', $name));
		
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
			$response = $this->guzzle->get($this->url('v2.2/matchhistory/'.$summonerId, $region, ['rankedQueues'=>'RANKED_SOLO_5x5']), [
				'timeout' => $this->timeout(),
				'exceptions' => true
			]);

			// echo $response->getStatusCode();
			// echo $response->getBody();
			$object = $response->json();

			$matches = new Collection;

			foreach ($object['matches'] as $m)
			{
				$matches->push($this->match->createForPlayerId($m,$summonerId));
			}

			$matches->sort(function($a,$b) {
				return ($a->timestamp() > $b->timestamp());
			});

			return $matches;
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
	 * @param array $parameters
	 *
	 * @return string
	 */
	protected function url($uri, $region, $parameters = array())
	{
		$parameters['api_key'] = $this->apiKey;
		$query = http_build_query($parameters);

		return 'https://' . $region . '.' . $this->baseUrl . $region . '/' .  $uri . '?'. $query;
	}

	protected function timeout()
	{
		return 10;
	}

	protected function handle(\Exception $e)
	{
		//temp
		throw $e;

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