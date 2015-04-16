<?php namespace AppTests\Unit\Services\GameApi\League;

use Mockery as m;

use App\Services\GameApi\League\Client;
use App\Contracts\Service\GameApi\League\Match;
use App\Contracts\Service\GameApi\Player;

use Illuminate\Contracts\Events\Dispatcher as Event;

use GuzzleHttp\Client as GuzzleClient;

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

/**
 * @coversDefaultClass \App\Services\GameApi\League\Client
 */
class ClientTest extends \AppTests\TestCase {

	private function getClient()
	{
		return new Client($this->app);
	}

	/**
	 * @small
	 *
	 * @group services
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 */
	public function testSummonerForNameInRegionWithNoArguments()
	{
		$player = $this->getMockOf(Player::class);
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);

		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200));

		$client = $this->getClient();

		$this->setExpectedException(\ErrorException::class);

		$result = $client->summonerForNameInRegion();
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 */
	public function testSummonerForNameInRegionWithNullParameters()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200));
		
		$client = $this->getClient();

		$this->setExpectedException(BadRequest::class);

		$result = $client->summonerForNameInRegion(null,null);
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 */
	public function testSummonerForNameInRegionWithOKResponse()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);

		$player->shouldReceive('create')->with(1,'bar')->andReturn($this->getMockOf(Player::class));
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200,[],'{"foofoo":{"name":"bar","id":1}}'));
		
		$client = $this->getClient();

		$result = $client->summonerForNameInRegion('foo Foo', 'na');

		$this->assertTrue(is_a($result, get_class($player)));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 */
	public function testSummonerForNameInRegionWithNotFoundResponse()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(404));
		
		$client = $this->getClient();

		$this->setExpectedException(PlayerNotFound::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithBadRequest()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(RequestWasInvalid::class);

		$this->app->instance(RequestWasInvalid::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(400));
		
		$client = $this->getClient();

		$this->setExpectedException(BadRequest::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithRateLimited()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(RateLimitWasExceeded::class);

		$this->app->instance(RateLimitWasExceeded::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(429));
		
		$client = $this->getClient();

		$this->setExpectedException(RateLimitExceeded::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithUnauthorized()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(AccessWasUnauthorized::class);

		$this->app->instance(AccessWasUnauthorized::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(401));
		
		$client = $this->getClient();

		$this->setExpectedException(AccessUnauthorized::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithServiceUnavailable()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(ServiceWasUnavailable::class);

		$this->app->instance(ServiceWasUnavailable::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(503));
		
		$client = $this->getClient();

		$this->setExpectedException(ServiceUnavailable::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithInternalServerError()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(ServerHadAnError::class);

		$this->app->instance(ServerHadAnError::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(500));
		
		$client = $this->getClient();

		$this->setExpectedException(InternalServerError::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithUnknownResponseError()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(UnknownErrorOccurred::class);

		$this->app->instance(UnknownErrorOccurred::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(428));
		
		$client = $this->getClient();

		$this->setExpectedException(UnknownError::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithBadJson()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(UnknownErrorOccurred::class);

		$this->app->instance(UnknownErrorOccurred::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200,[],'foo'));
		
		$client = $this->getClient();

		$this->setExpectedException(UnknownError::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::summonerForNameInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testSummonerForNameInRegionWithTooManyRedirects()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(ServerHadAnError::class);

		$this->app->instance(ServerHadAnError::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(302,['Location'=>'/'],'foo',10));
		
		$client = $this->getClient();

		$this->setExpectedException(InternalServerError::class);

		$result = $client->summonerForNameInRegion('foo Foo', 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 */
	public function testMatchHistoryForSummonerIdInRegionWithNoArguments()
	{
		$player = $this->getMockOf(Player::class);
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);

		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200));

		$client = $this->getClient();

		$this->setExpectedException(\ErrorException::class);

		$result = $client->matchHistoryForSummonerIdInRegion();
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 */
	public function testMatchHistoryForSummonerIdInRegionWithNullParameters()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200));
		
		$client = $this->getClient();

		$this->setExpectedException(BadRequest::class);

		$result = $client->matchHistoryForSummonerIdInRegion(null,null);
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 */
	public function testMatchHistoryForSummonerIdInRegionWithOKResponse()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);

		$resultMatch = $this->getMockOf(Match::class);
		$resultMatch->shouldReceive('timestamp')->times(2);
		$match->shouldReceive('createForPlayerId')->with(["foo"=>"bar"],1)->andReturn($resultMatch);
		$match->shouldReceive('createForPlayerId')->with(["bar"=>"baz"],1)->andReturn($resultMatch);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200,[],'{"matches":[{"foo":"bar"},{"bar":"baz"}]}'));
		
		$client = $this->getClient();

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');

		$this->assertTrue(is_a($result, \Illuminate\Support\Collection::class));
		$this->assertEquals($result->count(),2);
		$this->assertTrue(is_a($result->first(),get_class($match)));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithNotFoundResponse()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(404));
		
		$client = $this->getClient();

		$this->setExpectedException(MatchesNotFound::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');

		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(422));

		$client = $this->getClient();
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithNotFoundResponse2()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(422));
		
		$client = $this->getClient();

		$this->setExpectedException(MatchesNotFound::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithBadRequest()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(RequestWasInvalid::class);

		$this->app->instance(RequestWasInvalid::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(400));
		
		$client = $this->getClient();

		$this->setExpectedException(BadRequest::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithRateLimited()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(RateLimitWasExceeded::class);

		$this->app->instance(RateLimitWasExceeded::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(429));
		
		$client = $this->getClient();

		$this->setExpectedException(RateLimitExceeded::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithUnauthorized()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(AccessWasUnauthorized::class);

		$this->app->instance(AccessWasUnauthorized::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(401));
		
		$client = $this->getClient();

		$this->setExpectedException(AccessUnauthorized::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithServiceUnavailable()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(ServiceWasUnavailable::class);

		$this->app->instance(ServiceWasUnavailable::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(503));
		
		$client = $this->getClient();

		$this->setExpectedException(ServiceUnavailable::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithInternalServerError()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(ServerHadAnError::class);

		$this->app->instance(ServerHadAnError::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(500));
		
		$client = $this->getClient();

		$this->setExpectedException(InternalServerError::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithUnknownResponseError()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(UnknownErrorOccurred::class);

		$this->app->instance(UnknownErrorOccurred::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(428));
		
		$client = $this->getClient();

		$this->setExpectedException(UnknownError::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithBadJson()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(UnknownErrorOccurred::class);

		$this->app->instance(UnknownErrorOccurred::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(200,[],'foo'));
		
		$client = $this->getClient();

		$this->setExpectedException(UnknownError::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::matchHistoryForSummonerIdInRegion
	 * @covers ::url
	 * @covers ::handle
	 */
	public function testMatchHistoryForSummonerIdInRegionWithTooManyRedirects()
	{
		$match = $this->getMockOf(Match::class);
		$event = $this->getMockOf(Event::class);
		$player = $this->getMockOf(Player::class);
		$errorEvent = $this->getMockOf(ServerHadAnError::class);

		$this->app->instance(ServerHadAnError::class,$errorEvent);

		$event->shouldReceive('fire')->once()->with($errorEvent);
		
		$this->app->instance(Player::class,$player);
		$this->app->instance(Match::class,$match);
		$this->app->instance(Event::class,$event);
		$this->app->instance(GuzzleClient::class,$this->getGuzzleMock(302,['Location'=>'/'],'foo',10));
		
		$client = $this->getClient();

		$this->setExpectedException(InternalServerError::class);

		$result = $client->matchHistoryForSummonerIdInRegion(1, 'na');
	}

}
