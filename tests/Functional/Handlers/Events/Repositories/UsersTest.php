<?php namespace AppTests\Functional\Handlers\Events\Repositories;

use Mockery as m;
use App\Handlers\Events\Repositories\Users as Handler;
use Illuminate\Contracts\Events\Dispatcher as Events;
use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Models\User;
use Illuminate\Session\SessionManager as Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Commands\AggregateDataFromUserUpdate;
use App\Commands\NotifyAboutNewStreamer;
use App\Commands\SendEmailConfirmationRequest;

/**
 * @coversDefaultClass \App\Handlers\Events\Repositories\Users
 */
class UsersTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	/**
	 * Get an instance of the object being tested.
	 *
	 * @return Handler
	 */
	private function getHandler()
	{
		return $this->app->make(Handler::class);
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 */
	public function test_on_User_Was_Created_Without_Auid()
	{
		$session = $this->app->make(Session::class);

		// Make sure the key isn't in the session for whatever reason
		$session->forget('auid');

		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
		]);

		$event = new UserWasCreated($user);

		$handler = $this->getHandler();
		$handler->onUserWasCreated($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals($timestamp, $user->updated_at->timestamp);
		$this->assertNull($user->referred_by);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(SendEmailConfirmationRequest::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasCreated
	 */
	public function test_on_User_Was_Created_With_Auid_Without_Referrer()
	{
		$session = $this->app->make(Session::class);

		$session->put('auid',9999);

		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
		]);

		$event = new UserWasCreated($user);

		$handler = $this->getHandler();
		$handler->onUserWasCreated($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertNull($user->referred_by);
		$this->assertEquals($timestamp, $user->updated_at->timestamp);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(SendEmailConfirmationRequest::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasCreated
	 */
	public function test_on_User_Was_Created()
	{
		$session = $this->app->make(Session::class);

		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null
		]);

		$user2 = User::create([
			'email' => 'baz',
			'username' => 'foo',
			'referred_by' => null
		]);

		$session->put('auid',$user2->id);

		$event = new UserWasCreated($user);

		$handler = $this->getHandler();
		$handler->onUserWasCreated($event);

		$user = User::find($user->id);

		$this->assertEquals($user->referred_by,$user2->id);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(SendEmailConfirmationRequest::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasCreated
	 */
	public function test_on_User_Was_Created_with_email_confirmatoin()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'email_confirmed' => true,
		]);

		$event = new UserWasCreated($user);

		$handler = $this->getHandler();
		$handler->onUserWasCreated($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals($timestamp, $user->updated_at->timestamp);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasUpdated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasUpdated
	 */
	public function test_on_User_Was_Updated_without_streamer()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 0,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 0,
			'start_completed' => 0
		]);

		$event = new UserWasUpdated($user, ['summoner_id'=>0]);

		$handler = $this->getHandler();
		$handler->onUserWasUpdated($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals($timestamp, $user->updated_at->timestamp);
		$this->assertEquals(0,$user->streamer_completed);
		$this->assertEquals(0,$user->start_completed);
		$this->assertNull($user->short_url);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasUpdated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasUpdated
	 */
	public function test_on_User_Was_Updated_without_twitch_id()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 0,
			'summoner_id' => 1,
			'streamer_completed' => 0,
			'start_completed' => 0
		]);

		$event = new UserWasUpdated($user, ['summoner_id'=>0]);

		$handler = $this->getHandler();
		$handler->onUserWasUpdated($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals($timestamp, $user->updated_at->timestamp);
		$this->assertEquals(0,$user->streamer_completed);
		$this->assertEquals(0,$user->start_completed);
		$this->assertNull($user->short_url);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasUpdated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasUpdated
	 */
	public function test_on_User_Was_Updated_without_summoner_id()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 0,
			'streamer_completed' => 0,
			'start_completed' => 0,
		]);

		$event = new UserWasUpdated($user, ['twitch_id'=>0]);

		$handler = $this->getHandler();
		$handler->onUserWasUpdated($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals($timestamp, $user->updated_at->timestamp);
		$this->assertEquals(0,$user->streamer_completed);
		$this->assertEquals(0,$user->start_completed);
		$this->assertNull($user->short_url);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasUpdated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasUpdated
	 */
	public function test_on_User_Was_Updated_with_streamer_completed()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 1,
			'start_completed' => 0,
			'short_url' => 'baz',
		]);
		$date = new Carbon('2011-11-11 11:11:11');
		DB::table($user->getTable())->whereId($user->id)->update(['updated_at'=>$date]);

		$event = new UserWasUpdated($user, ['streamer_completed'=>0]);

		$handler = $this->getHandler();
		$handler->onUserWasUpdated($event);

		$user = User::find($user->id);

		$this->assertEquals($date->timestamp,$user->updated_at->timestamp);
		$this->assertEquals('baz', $user->short_url);
		$this->assertEquals(0, $user->start_completed);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasUpdated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasUpdated
	 */
	public function test_on_User_Was_Updated_completing_streamer()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 0,
			'start_completed' => 0,
			'short_url' => null,
		]);

		$event = new UserWasUpdated($user, ['summoner_id'=>0]);

		$this->mockGuzzle([
			201,
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
		], [
			'{"url":"baz"}'
		]);

		$handler = $this->getHandler();
		$handler->onUserWasUpdated($event);

		$user = User::find($user->id);

		$this->assertEquals(1,$user->streamer_completed);
		$this->assertEquals(1,$user->start_completed);
		$this->assertEquals('baz', $user->short_url);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(NotifyAboutNewStreamer::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onUserWasUpdated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Events\Repositories\UserWasUpdated
	 */
	public function test_on_User_Was_Updated_changing_earnings()
	{
		$date = new Carbon('2011-11-11 11:11:11');

		DB::table('users')->insert([
			'email' => 'foo',
			'username' => 'bar',
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 1,
			'start_completed' => 0,
			'short_url' => null,
			'earnings' => 10,
			'created_at' => $date,
			'updated_at' => $date,
		]);
		$user = User::first();

		$event = new UserWasUpdated($user, ['earnings'=>0, 'updated_at'=>Carbon::now()]);
		
		$handler = $this->getHandler();
		$handler->onUserWasUpdated($event);

		$user = User::find($user->id);

		$this->assertEquals(0, $user->start_completed);
		$this->assertNull($user->short_url);
		
		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(AggregateDataFromUserUpdate::class, get_class($command));
	}

}
