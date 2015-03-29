<?php namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Contracts\Service\GameApi\League\Client;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use App\Contracts\Service\Gurus\Champion as ChampionGuru;
use Carbon\Carbon;

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
		$this->middleware('auth');
		$this->middleware('ajax');

		$this->client = $client;
		$this->response = $response;
		$this->guru = $guru;
	}

	public function getSummoner($name, $region)
	{
		$summoner = $this->client->summonerForNameInRegion($name,$region);

		if (!$summoner)
		{

		}
		else
		{
			$matches = $this->client->matchHistoryForSummonerIdInRegion($summoner->id(),$region);

			if (!$matches)
			{

			}
			else
			{
				$match = $matches->last();

				return $this->response->json(['summoner'=>['id'=>$summoner->id()],'match'=>['win'=>$match->win(),'champion'=>$this->guru->name($match->champion()),'ago'=> Carbon::createFromTimestamp($match->timestamp())->diffForHumans()]]);
			}
		}
	}

}