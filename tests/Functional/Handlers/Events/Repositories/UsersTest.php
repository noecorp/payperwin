<?php namespace AppTests\Functional\Handlers\Events\Repositories;

use Mockery as m;
use App\Handlers\Events\Repositories\Pledges;
use Illuminate\Contracts\Events\Dispatcher as Events;
use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Models\User;
use Illuminate\Session\SessionManager as Session;
use Carbon\Carbon;

class UsersTest extends \AppTests\TestCase {

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

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertNull($user->referred_by);
	}

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

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertNull($user->referred_by);
	}

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

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals($user->referred_by,$user2->id);
	}

	public function test_on_User_Was_Updated_without_streamer()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 0,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 0
		]);

		$event = new UserWasUpdated($user);

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals(0,$user->streamer_completed);
	}

	public function test_on_User_Was_Updated_without_twitch_id()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 0,
			'summoner_id' => 1,
			'streamer_completed' => 0
		]);

		$event = new UserWasUpdated($user);

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals(0,$user->streamer_completed);
	}

	public function test_on_User_Was_Updated_without_summoner_id()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 0,
			'streamer_completed' => 0
		]);

		$event = new UserWasUpdated($user);

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals(0,$user->streamer_completed);
	}

	public function test_on_User_Was_Updated_with_streamer_completed()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 1
		]);
		$date = new Carbon('2011-11-11 11:11:11');
		\Illuminate\Support\Facades\DB::table($user->getTable())->whereId($user->id)->update(['updated_at'=>$date]);

		$event = new UserWasUpdated($user);

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$user = User::find($user->id);

		$this->assertEquals($date->timestamp,$user->updated_at->timestamp);
	}

	public function test_on_User_Was_Updated()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referred_by' => null,
			'streamer' => 1,
			'twitch_id' => 1,
			'summoner_id' => 1,
			'streamer_completed' => 0
		]);

		$event = new UserWasUpdated($user);

		//We'll use the service container to also make sure that event subscriptions go through
		$this->app->make(Events::class)->fire($event);

		$timestamp = $user->updated_at->timestamp;

		$user = User::find($user->id);

		$this->assertEquals(1,$user->streamer_completed);
	}

}
