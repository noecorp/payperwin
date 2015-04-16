<?php namespace AppTests\Functional\Controllers\Clients;

use Mockery as m;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\User;

/**
 * @coversDefaultClass \App\Http\Controllers\Clients\League
 */
class LeagueTest extends \AppTests\TestCase {

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerAbortsWithMissingArgument()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'foo'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(422);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->region[0],'The region field is required.');
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerRedirectsWhenNotLoggedIn()
	{
		$response = $this->call('GET','clients/league/summoner/',['summoner_name'=>'foo','region'=>'bar'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->redirect,url('auth/login'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerAbortsWhenNotAjax()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'foo','region'=>'bar']);

		$this->assertResponseStatus(400);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Bad Request');
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerOKWithValidData()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$guzzle = $this->getGuzzleMock([
			200,
			200
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8']
		], [
			$this->getOkSummonerData(),
			$this->getOkMatchData()
		]);

		$this->app->instance(GuzzleClient::class,$guzzle);

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseOk();
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson(true)['summoner'],['id'=>19887289]);
		$this->assertEquals($this->responseJson(true)['match'],['win'=>false,'champion'=>'Kalista','ago'=>Carbon::createFromTimestamp(round(1428526298662/1000))->diffForHumans()]);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerNameNotFound()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$guzzle = $this->getGuzzleMock(404, ['Content-Type'=>'application/json; charset=UTF-8'], '{}');

		$this->app->instance(GuzzleClient::class,$guzzle);

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie102391283','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(404);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Not Found');
		$this->assertEquals($this->responseJson()->reason,'summoner');
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerMatchesNotFound()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$guzzle = $this->getGuzzleMock([
			200,
			404,
			200,
			422
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8']
		], [
			$this->getOkSummonerData(),
			'{}',
			$this->getOkSummonerData(),
			'{}'
		]);

		$this->app->instance(GuzzleClient::class,$guzzle);

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(404);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Not Found');
		$this->assertEquals($this->responseJson()->reason,'matches');

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(404);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Not Found');
		$this->assertEquals($this->responseJson()->reason,'matches');
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getSummoner
	 */
	public function testGetSummonerNameErrors()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$guzzle = $this->getGuzzleMock([
			400,
			401,
			429,
			500,
			503,
			428
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8'],
			['Content-Type'=>'application/json; charset=UTF-8']
		], [
			'{}',
			'{}',
			'{}',
			'{}',
			'{}',
			'{}'
		]);

		$this->app->instance(GuzzleClient::class,$guzzle);

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(400);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Bad Request');

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(400);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Bad Request');

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(400);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Bad Request');

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(500);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Internal Server Error');
		$this->assertEquals($this->responseJson()->reason,'API Access Error');

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(500);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Internal Server Error');
		$this->assertEquals($this->responseJson()->reason,'API Access Error');

		$response = $this->call('GET','clients/league/summoner',['summoner_name'=>'Imaqtpie','region'=>'na'],[],[],['HTTP_X-Requested-With'=>'XMLHttpRequest']);

		$this->assertResponseStatus(500);
		$this->assertResponseIsJson();
		$this->assertEquals($this->responseJson()->error,'Internal Server Error');
		$this->assertEquals($this->responseJson()->reason,'Unknown Error');
	}

	private function getOkSummonerData()
	{
		return '{"imaqtpie": {
	"id": 19887289,
	"name": "Imaqtpie",
	"profileIconId": 784,
	"revisionDate": 1428528220000,
	"summonerLevel": 30
}}';
	}

	private function getOkMatchData()
	{
		return '{"matches": [
	{
		"matchVersion": "5.7.0.275",
		"region": "NA",
		"mapId": 11,
		"season": "SEASON2015",
		"queueType": "RANKED_SOLO_5x5",
		"matchDuration": 2225,
		"matchCreation": 1428520655109,
		"matchType": "MATCHED_GAME",
		"matchId": 1788933376,
		"participants": [{
			"masteries": [
				{
					"rank": 4,
					"masteryId": 4112
				},
				{
					"rank": 1,
					"masteryId": 4114
				},
				{
					"rank": 3,
					"masteryId": 4122
				},
				{
					"rank": 1,
					"masteryId": 4124
				},
				{
					"rank": 1,
					"masteryId": 4131
				},
				{
					"rank": 1,
					"masteryId": 4132
				},
				{
					"rank": 3,
					"masteryId": 4134
				},
				{
					"rank": 1,
					"masteryId": 4141
				},
				{
					"rank": 1,
					"masteryId": 4144
				},
				{
					"rank": 1,
					"masteryId": 4151
				},
				{
					"rank": 3,
					"masteryId": 4152
				},
				{
					"rank": 1,
					"masteryId": 4162
				},
				{
					"rank": 2,
					"masteryId": 4211
				},
				{
					"rank": 2,
					"masteryId": 4212
				},
				{
					"rank": 1,
					"masteryId": 4221
				},
				{
					"rank": 3,
					"masteryId": 4222
				},
				{
					"rank": 1,
					"masteryId": 4232
				}
			],
			"stats": {
				"unrealKills": 0,
				"item2": 3151,
				"item1": 3020,
				"totalDamageTaken": 25780,
				"item0": 3135,
				"pentaKills": 0,
				"sightWardsBoughtInGame": 0,
				"winner": true,
				"magicDamageDealt": 122144,
				"wardsKilled": 2,
				"largestCriticalStrike": 541,
				"trueDamageDealt": 5870,
				"doubleKills": 1,
				"physicalDamageDealt": 86949,
				"tripleKills": 0,
				"deaths": 5,
				"firstBloodAssist": false,
				"magicDamageDealtToChampions": 26088,
				"assists": 9,
				"visionWardsBoughtInGame": 0,
				"totalTimeCrowdControlDealt": 66,
				"champLevel": 17,
				"physicalDamageTaken": 13034,
				"totalDamageDealt": 214964,
				"largestKillingSpree": 4,
				"inhibitorKills": 1,
				"minionsKilled": 242,
				"towerKills": 2,
				"physicalDamageDealtToChampions": 10852,
				"quadraKills": 0,
				"goldSpent": 14253,
				"totalDamageDealtToChampions": 38559,
				"goldEarned": 16070,
				"neutralMinionsKilledTeamJungle": 12,
				"firstBloodKill": false,
				"firstTowerKill": false,
				"wardsPlaced": 2,
				"trueDamageDealtToChampions": 1617,
				"killingSprees": 3,
				"firstInhibitorKill": false,
				"totalScoreRank": 0,
				"totalUnitsHealed": 4,
				"kills": 11,
				"firstInhibitorAssist": false,
				"totalPlayerScore": 0,
				"neutralMinionsKilledEnemyJungle": 12,
				"magicDamageTaken": 11019,
				"largestMultiKill": 2,
				"totalHeal": 3688,
				"item4": 3078,
				"item3": 1055,
				"objectivePlayerScore": 0,
				"item6": 3342,
				"firstTowerAssist": false,
				"item5": 3285,
				"trueDamageTaken": 1727,
				"neutralMinionsKilled": 24,
				"combatPlayerScore": 0
			},
			"runes": [
				{
					"rank": 9,
					"runeId": 5245
				},
				{
					"rank": 4,
					"runeId": 5289
				},
				{
					"rank": 5,
					"runeId": 5301
				},
				{
					"rank": 9,
					"runeId": 5317
				},
				{
					"rank": 3,
					"runeId": 5337
				}
			],
			"timeline": {
				"xpDiffPerMinDeltas": {
					"zeroToTen": 16.249999999999986,
					"thirtyToEnd": 85.00000000000006,
					"tenToTwenty": 160.40000000000006,
					"twentyToThirty": -295.09999999999997
				},
				"damageTakenDiffPerMinDeltas": {
					"zeroToTen": 12.799999999999983,
					"thirtyToEnd": -155.39999999999998,
					"tenToTwenty": -48.25000000000003,
					"twentyToThirty": 22.149999999999977
				},
				"xpPerMinDeltas": {
					"zeroToTen": 320.6,
					"thirtyToEnd": 601,
					"tenToTwenty": 634,
					"twentyToThirty": 410.4
				},
				"goldPerMinDeltas": {
					"zeroToTen": 235.6,
					"thirtyToEnd": 474.2,
					"tenToTwenty": 562.8,
					"twentyToThirty": 377.5
				},
				"role": "DUO_CARRY",
				"creepsPerMinDeltas": {
					"zeroToTen": 6.800000000000001,
					"thirtyToEnd": 5,
					"tenToTwenty": 8.4,
					"twentyToThirty": 5.5
				},
				"csDiffPerMinDeltas": {
					"zeroToTen": -0.6000000000000001,
					"thirtyToEnd": -1,
					"tenToTwenty": 0.3500000000000001,
					"twentyToThirty": 0.5499999999999999
				},
				"damageTakenPerMinDeltas": {
					"zeroToTen": 298.9,
					"thirtyToEnd": 1055.4,
					"tenToTwenty": 669.1,
					"twentyToThirty": 952.7
				},
				"lane": "BOTTOM"
			},
			"spell2Id": 7,
			"participantId": 0,
			"championId": 42,
			"teamId": 100,
			"highestAchievedSeasonTier": "CHALLENGER",
			"spell1Id": 4
		}],
		"matchMode": "CLASSIC",
		"platformId": "NA1",
		"participantIdentities": [{
			"player": {
				"profileIcon": 784,
				"matchHistoryUri": "/v1/stats/player_history/NA/32639237",
				"summonerName": "Imaqtpie",
				"summonerId": 19887289
			},
			"participantId": 0
		}]
	},
	{
		"matchVersion": "5.7.0.275",
		"region": "NA",
		"mapId": 11,
		"season": "SEASON2015",
		"queueType": "RANKED_SOLO_5x5",
		"matchDuration": 2167,
		"matchCreation": 1428523515184,
		"matchType": "MATCHED_GAME",
		"matchId": 1788962900,
		"participants": [{
			"masteries": [
				{
					"rank": 4,
					"masteryId": 4112
				},
				{
					"rank": 1,
					"masteryId": 4114
				},
				{
					"rank": 3,
					"masteryId": 4122
				},
				{
					"rank": 1,
					"masteryId": 4124
				},
				{
					"rank": 1,
					"masteryId": 4131
				},
				{
					"rank": 1,
					"masteryId": 4132
				},
				{
					"rank": 3,
					"masteryId": 4134
				},
				{
					"rank": 1,
					"masteryId": 4141
				},
				{
					"rank": 1,
					"masteryId": 4144
				},
				{
					"rank": 1,
					"masteryId": 4151
				},
				{
					"rank": 3,
					"masteryId": 4152
				},
				{
					"rank": 1,
					"masteryId": 4162
				},
				{
					"rank": 2,
					"masteryId": 4211
				},
				{
					"rank": 2,
					"masteryId": 4212
				},
				{
					"rank": 1,
					"masteryId": 4221
				},
				{
					"rank": 3,
					"masteryId": 4222
				},
				{
					"rank": 1,
					"masteryId": 4232
				}
			],
			"stats": {
				"unrealKills": 0,
				"item2": 1038,
				"item1": 3140,
				"totalDamageTaken": 33243,
				"item0": 3153,
				"pentaKills": 0,
				"sightWardsBoughtInGame": 0,
				"winner": true,
				"magicDamageDealt": 69,
				"wardsKilled": 4,
				"largestCriticalStrike": 1272,
				"trueDamageDealt": 25415,
				"doubleKills": 1,
				"physicalDamageDealt": 213586,
				"tripleKills": 0,
				"deaths": 7,
				"firstBloodAssist": false,
				"magicDamageDealtToChampions": 69,
				"assists": 17,
				"visionWardsBoughtInGame": 1,
				"totalTimeCrowdControlDealt": 323,
				"champLevel": 18,
				"physicalDamageTaken": 22239,
				"totalDamageDealt": 239071,
				"largestKillingSpree": 3,
				"inhibitorKills": 0,
				"minionsKilled": 263,
				"towerKills": 2,
				"physicalDamageDealtToChampions": 29484,
				"quadraKills": 0,
				"goldSpent": 14680,
				"totalDamageDealtToChampions": 37957,
				"goldEarned": 15600,
				"neutralMinionsKilledTeamJungle": 30,
				"firstBloodKill": false,
				"firstTowerKill": false,
				"wardsPlaced": 3,
				"trueDamageDealtToChampions": 8403,
				"killingSprees": 1,
				"firstInhibitorKill": false,
				"totalScoreRank": 0,
				"totalUnitsHealed": 3,
				"kills": 6,
				"firstInhibitorAssist": false,
				"totalPlayerScore": 0,
				"neutralMinionsKilledEnemyJungle": 6,
				"magicDamageTaken": 10249,
				"largestMultiKill": 2,
				"totalHeal": 4524,
				"item4": 3031,
				"item3": 3006,
				"objectivePlayerScore": 0,
				"item6": 3342,
				"firstTowerAssist": true,
				"item5": 3046,
				"trueDamageTaken": 755,
				"neutralMinionsKilled": 36,
				"combatPlayerScore": 0
			},
			"runes": [
				{
					"rank": 9,
					"runeId": 5245
				},
				{
					"rank": 4,
					"runeId": 5289
				},
				{
					"rank": 5,
					"runeId": 5301
				},
				{
					"rank": 9,
					"runeId": 5317
				},
				{
					"rank": 3,
					"runeId": 5337
				}
			],
			"timeline": {
				"xpDiffPerMinDeltas": {
					"zeroToTen": 24.70000000000003,
					"thirtyToEnd": 69.59999999999997,
					"tenToTwenty": -37.14999999999998,
					"twentyToThirty": 93.59999999999994
				},
				"damageTakenDiffPerMinDeltas": {
					"zeroToTen": -52.85,
					"thirtyToEnd": -47.899999999999864,
					"tenToTwenty": 124.04999999999998,
					"twentyToThirty": 70.04999999999995
				},
				"xpPerMinDeltas": {
					"zeroToTen": 330,
					"thirtyToEnd": 617.6,
					"tenToTwenty": 513.8,
					"twentyToThirty": 639.9
				},
				"goldPerMinDeltas": {
					"zeroToTen": 265.3,
					"thirtyToEnd": 485.6,
					"tenToTwenty": 375.6,
					"twentyToThirty": 546.1
				},
				"role": "DUO_CARRY",
				"creepsPerMinDeltas": {
					"zeroToTen": 8,
					"thirtyToEnd": 7.2,
					"tenToTwenty": 7.8,
					"twentyToThirty": 6.3
				},
				"csDiffPerMinDeltas": {
					"zeroToTen": 0.44999999999999973,
					"thirtyToEnd": 0.9000000000000004,
					"tenToTwenty": 0.10000000000000009,
					"twentyToThirty": 0.30000000000000004
				},
				"damageTakenPerMinDeltas": {
					"zeroToTen": 174.89999999999998,
					"thirtyToEnd": 1659.2,
					"tenToTwenty": 769.6,
					"twentyToThirty": 1267.4
				},
				"lane": "BOTTOM"
			},
			"spell2Id": 7,
			"participantId": 0,
			"championId": 67,
			"teamId": 100,
			"highestAchievedSeasonTier": "CHALLENGER",
			"spell1Id": 4
		}],
		"matchMode": "CLASSIC",
		"platformId": "NA1",
		"participantIdentities": [{
			"player": {
				"profileIcon": 784,
				"matchHistoryUri": "/v1/stats/player_history/NA/32639237",
				"summonerName": "Imaqtpie",
				"summonerId": 19887289
			},
			"participantId": 0
		}]
	},
	{
		"matchVersion": "5.7.0.275",
		"region": "NA",
		"mapId": 11,
		"season": "SEASON2015",
		"queueType": "RANKED_SOLO_5x5",
		"matchDuration": 1628,
		"matchCreation": 1428526298662,
		"matchType": "MATCHED_GAME",
		"matchId": 1788991984,
		"participants": [{
			"masteries": [
				{
					"rank": 4,
					"masteryId": 4112
				},
				{
					"rank": 1,
					"masteryId": 4114
				},
				{
					"rank": 3,
					"masteryId": 4122
				},
				{
					"rank": 1,
					"masteryId": 4124
				},
				{
					"rank": 1,
					"masteryId": 4131
				},
				{
					"rank": 1,
					"masteryId": 4132
				},
				{
					"rank": 3,
					"masteryId": 4134
				},
				{
					"rank": 1,
					"masteryId": 4141
				},
				{
					"rank": 1,
					"masteryId": 4144
				},
				{
					"rank": 1,
					"masteryId": 4151
				},
				{
					"rank": 3,
					"masteryId": 4152
				},
				{
					"rank": 1,
					"masteryId": 4162
				},
				{
					"rank": 2,
					"masteryId": 4211
				},
				{
					"rank": 2,
					"masteryId": 4212
				},
				{
					"rank": 1,
					"masteryId": 4221
				},
				{
					"rank": 3,
					"masteryId": 4222
				},
				{
					"rank": 1,
					"masteryId": 4232
				}
			],
			"stats": {
				"unrealKills": 0,
				"item2": 1055,
				"item1": 3006,
				"totalDamageTaken": 16147,
				"item0": 3085,
				"pentaKills": 0,
				"sightWardsBoughtInGame": 0,
				"winner": false,
				"magicDamageDealt": 2576,
				"wardsKilled": 3,
				"largestCriticalStrike": 0,
				"trueDamageDealt": 1261,
				"doubleKills": 2,
				"physicalDamageDealt": 91881,
				"tripleKills": 0,
				"deaths": 6,
				"firstBloodAssist": false,
				"magicDamageDealtToChampions": 1076,
				"assists": 7,
				"visionWardsBoughtInGame": 2,
				"totalTimeCrowdControlDealt": 359,
				"champLevel": 12,
				"physicalDamageTaken": 10381,
				"totalDamageDealt": 95718,
				"largestKillingSpree": 5,
				"inhibitorKills": 0,
				"minionsKilled": 132,
				"towerKills": 1,
				"physicalDamageDealtToChampions": 9272,
				"quadraKills": 0,
				"goldSpent": 8880,
				"totalDamageDealtToChampions": 10924,
				"goldEarned": 9573,
				"neutralMinionsKilledTeamJungle": 15,
				"firstBloodKill": false,
				"firstTowerKill": false,
				"wardsPlaced": 3,
				"trueDamageDealtToChampions": 576,
				"killingSprees": 1,
				"firstInhibitorKill": false,
				"totalScoreRank": 0,
				"totalUnitsHealed": 2,
				"kills": 6,
				"firstInhibitorAssist": false,
				"totalPlayerScore": 0,
				"neutralMinionsKilledEnemyJungle": 2,
				"magicDamageTaken": 4787,
				"largestMultiKill": 2,
				"totalHeal": 1223,
				"item4": 3072,
				"item3": 1037,
				"objectivePlayerScore": 0,
				"item6": 3342,
				"firstTowerAssist": false,
				"item5": 1036,
				"trueDamageTaken": 979,
				"neutralMinionsKilled": 17,
				"combatPlayerScore": 0
			},
			"runes": [
				{
					"rank": 9,
					"runeId": 5245
				},
				{
					"rank": 4,
					"runeId": 5289
				},
				{
					"rank": 5,
					"runeId": 5301
				},
				{
					"rank": 9,
					"runeId": 5317
				},
				{
					"rank": 3,
					"runeId": 5337
				}
			],
			"timeline": {
				"xpDiffPerMinDeltas": {
					"zeroToTen": 72.59999999999997,
					"tenToTwenty": -85.85000000000002
				},
				"damageTakenDiffPerMinDeltas": {
					"zeroToTen": 71.95000000000003,
					"tenToTwenty": 421.35
				},
				"xpPerMinDeltas": {
					"zeroToTen": 299.79999999999995,
					"tenToTwenty": 332.2
				},
				"goldPerMinDeltas": {
					"zeroToTen": 375.9,
					"tenToTwenty": 344.5
				},
				"role": "DUO_CARRY",
				"creepsPerMinDeltas": {
					"zeroToTen": 4.9,
					"tenToTwenty": 4.8
				},
				"csDiffPerMinDeltas": {
					"zeroToTen": 0.44999999999999973,
					"tenToTwenty": -0.6000000000000001
				},
				"damageTakenPerMinDeltas": {
					"zeroToTen": 427.5,
					"tenToTwenty": 673.8
				},
				"lane": "BOTTOM"
			},
			"spell2Id": 7,
			"participantId": 0,
			"championId": 429,
			"teamId": 100,
			"highestAchievedSeasonTier": "CHALLENGER",
			"spell1Id": 4
		}],
		"matchMode": "CLASSIC",
		"platformId": "NA1",
		"participantIdentities": [{
			"player": {
				"profileIcon": 784,
				"matchHistoryUri": "/v1/stats/player_history/NA/32639237",
				"summonerName": "Imaqtpie",
				"summonerId": 19887289
			},
			"participantId": 0
		}]
	}
]}';
	}

}
