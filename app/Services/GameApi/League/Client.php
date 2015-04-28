<?php namespace App\Services\GameApi\League;

use App\Contracts\Service\GameApi\League\Client as ClientInterface;

use App\Contracts\Service\GameApi\Player;
use App\Contracts\Service\GameApi\League\Match;
use Illuminate\Contracts\Events\Dispatcher as Event;
use GuzzleHttp\Client as Guzzle;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TooManyRedirectsException;

use App\Exceptions\Services\GameApi\PlayerNotFound;
use App\Exceptions\Services\GameApi\MatchesNotFound;
use App\Exceptions\Services\GameApi\RateLimitExceeded;
use App\Exceptions\Services\GameApi\AccessUnauthorized;
use App\Exceptions\Services\GameApi\InternalServerError;
use App\Exceptions\Services\GameApi\ServiceUnavailable;
use App\Exceptions\Services\GameApi\UnknownError;
use App\Exceptions\Services\GameApi\BadRequest;

use App\Events\Services\GameApi\RateLimitWasExceeded;
use App\Events\Services\GameApi\AccessWasUnauthorized;
use App\Events\Services\GameApi\ServiceWasUnavailable;
use App\Events\Services\GameApi\RequestWasInvalid;
use App\Events\Services\GameApi\ServerHadAnError;
use App\Events\Services\GameApi\UnknownErrorOccurred;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Container\Container;

class Client implements ClientInterface {

	protected $guzzle;

	protected $baseUrl;

	protected $apiKey;

	protected $player;
	protected $match;

	protected $event;

	protected $url;

	protected $app;

	public function __construct(Container $container)
	{
		$this->baseUrl = 'api.pvp.net/api/lol/';

		$this->apiKey = config('services.riot.key');

		$this->app = $container;
		$this->guzzle = $this->app->make(Guzzle::class);
		$this->player = $this->app->make(Player::class);
		$this->match = $this->app->make(Match::class);
		$this->event = $this->app->make(Event::class);
	}

	/**
	 * {@inheritdoc}
	 */
	public function summonerForNameInRegion($name, $region)
	{
		if (!$name || !$region)
		{
			throw new BadRequest;
		}

		$this->url = $this->url('v1.4/summoner/by-name/'.$name, $region);

		$name = strtolower(str_replace(' ', '', $name));
		
		try
		{
			$response = $this->guzzle->get($this->url, [
				'timeout' => $this->timeout(),
				'connect_timeout' => $this->timeout(),
				'exceptions' => true
			]);

			$object = $response->json();

			return $this->player->create($object[$name]['id'],$object[$name]['name']);
		}
		catch (ClientException $e)
		{
			switch ($e->getResponse()->getStatusCode()) {
				case 404:
					throw new PlayerNotFound;
					break;
				default:
					return $this->handle($e);
					break;
			}
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
		if (!$summonerId || !$region)
		{
			throw new BadRequest;
		}

		$this->url = $this->url('v2.2/matchhistory/'.$summonerId, $region, ['rankedQueues'=>'RANKED_SOLO_5x5']);

		try
		{
			$response = $this->guzzle->get($this->url, [
				'timeout' => $this->timeout(),
				'connect_timeout' => $this->timeout(),
				'exceptions' => true
			]);

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
		catch (ClientException $e)
		{
			switch ($e->getResponse()->getStatusCode()) {
				case 404:
				case 422:
					throw new MatchesNotFound;
					break;
				default:
					return $this->handle($e);
					break;
			}
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
		if ($e instanceof TooManyRedirectsException)
		{
			$this->event->fire($this->app->make(ServerHadAnError::class,['league',$this->url]));
			throw new InternalServerError;
		}
		else if ($e instanceof RequestException)
		{
			if (is_object($e->getResponse()))
			{
				switch ($e->getResponse()->getStatusCode())
				{
					case 400:
						$this->event->fire($this->app->make(RequestWasInvalid::class,['league',$this->url]));
						throw new BadRequest;
						break;
					case 429:
						$this->event->fire($this->app->make(RateLimitWasExceeded::class,['league',$this->url]));
						throw new RateLimitExceeded;
						break;
					case 401:
						$this->event->fire($this->app->make(AccessWasUnauthorized::class,['league',$this->url]));
						throw new AccessUnauthorized;
						break;
					case 503:
						$this->event->fire($this->app->make(ServiceWasUnavailable::class,['league',$this->url]));
						throw new ServiceUnavailable;
						break;
					case 500:
						$this->event->fire($this->app->make(ServerHadAnError::class,['league',$this->url]));
						throw new InternalServerError;
						break;
					default:
						$this->event->fire($this->app->make(UnknownErrorOccurred::class,['league',$this->url]));
						throw new UnknownError($e->getResponse()->getReasonPhrase());
						break;
				}
			}
		}
		
		$this->event->fire($this->app->make(UnknownErrorOccurred::class,['league',$this->url,['message'=>$e->getMessage()]]));
		throw new UnknownError($e->getMessage());
	}
}
