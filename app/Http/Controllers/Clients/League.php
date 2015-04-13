<?php namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Contracts\Service\GameApi\League\Client;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use App\Contracts\Service\Gurus\Champion as ChampionGuru;
use Carbon\Carbon;
use App\Exceptions\Services\GameApi\PlayerNotFound;
use App\Exceptions\Services\GameApi\MatchesNotFound;
use App\Exceptions\Services\GameApi\ClientError;
use App\Exceptions\Services\GameApi\ServerError;

class League extends Controller {

	/**
	 * League game api client instance.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Response factory instance.
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Champion Guru instance.
	 *
	 * @var ChampionGuru
	 */
	protected $guru;

	/**
	 * Create a new league client controller instance.
	 *
	 * @param Client $client
	 * @param Response $response
	 * @param ChampionGuru $guru
	 *
	 * @return void
	 */
	public function __construct(Client $client, Response $response, ChampionGuru $guru)
	{
		$this->middleware('json');
		$this->middleware('auth');
		$this->middleware('ajax');

		$this->client = $client;
		$this->response = $response;
		$this->guru = $guru;
	}

	/**
	 * Return summoner info from the Riot API.
	 *
	 * @param string $name
	 * @param string $region
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getSummoner(\App\Http\Requests\SearchForSummoner $request)
	{
		try
		{
			$summoner = $this->client->summonerForNameInRegion($request->get('summoner_name'),$request->get('region'));
		}
		catch (PlayerNotFound $e)
		{
			return abort(404, 'summoner');
		}
		catch (ClientError $e)
		{
			return abort(400);
		}
		catch (ServerError $e)
		{
			return abort(500, 'API Access Error');
		}
		catch (\Exception $e)
		{
			return abort(500, 'Unknown Error');
		}
		
		try
		{
			$matches = $this->client->matchHistoryForSummonerIdInRegion($summoner->id(),$request->get('region'));
		}
		catch (MatchesNotFound $e)
		{
			return abort(404, 'matches');
		}
		catch (ClientError $e)
		{
			return abort(400);
		}
		catch (ServerError $e)
		{
			return abort(500, 'API Access Error');
		}
		catch (\Exception $e)
		{
			return abort(500, 'Unknown Error');
		}

		$match = $matches->last();

		return $this->response->json([
			'summoner' => [
				'id' => $summoner->id()
			],
			'match' => [
				'win' => $match->win(),
				'champion' => $this->guru->name($match->champion()),
				'ago' => Carbon::createFromTimestamp($match->timestamp())->diffForHumans()
			]
		]);
	}

}
