<?php namespace AppTests\Functional\Handlers\Events\Repositories;

use Mockery as m;
use App\Handlers\Events\Repositories\Pledges as Handler;
use Illuminate\Contracts\Events\Dispatcher as Events;
use App\Events\Repositories\PledgeWasCreated;
use App\Models\Pledge;
use App\Models\User;
use App\Commands\AggregateDataFromPledge;
use Illuminate\Support\Facades\DB;

/**
 * @coversDefaultClass \App\Handlers\Events\Repositories\Pledges
 */
class PledgesTest extends \AppTests\TestCase {

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
	 * @covers ::onPledgeWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Models\Pledge
	 */
	public function test_on_Pledge_Was_Created_Without_Referred_By()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referrals' => 0,
			'start_completed' => 1,
		]);

		$streamer = User::create([
			'email' => 'baz',
			'username' => 'foo',
			'referred_by' => null,
			'referral_completed' => 0
		]);

		$pledge = Pledge::create([
			'user_id'=>$user->id,
			'streamer_id'=>$streamer->id,
			'amount'=>3,
			'type'=>1,
			'message'=>'yeh'
		]);

		$event = new PledgeWasCreated($pledge);

		$handler = $this->getHandler();
		$handler->onPledgeWasCreated($event);

		$timestamp1 = $streamer->updated_at->timestamp;
		$timestamp2 = $user->updated_at->timestamp;

		$streamer = User::find($streamer->id);
		$user = User::find($user->id);

		$this->assertEquals(0,$streamer->referral_completed);
		$this->assertEquals($timestamp1,$streamer->updated_at->timestamp);
		$this->assertEquals(0,$user->referrals);
		$this->assertEquals($timestamp2,$user->updated_at->timestamp);
		$this->assertEquals(1,$user->start_completed);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(AggregateDataFromPledge::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onPledgeWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Models\Pledge
	 */
	public function test_on_Pledge_Was_Created_With_Referral_Completed()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referrals' => 0,
		]);

		$streamer = User::create([
			'email' => 'baz',
			'username' => 'foo',
			'referred_by' => $user->id,
			'referral_completed' => 1
		]);

		$pledge = Pledge::create([
			'user_id'=>$user->id,
			'streamer_id'=>$streamer->id,
			'amount'=>3,
			'type'=>1,
			'message'=>'yeh'
		]);

		$event = new PledgeWasCreated($pledge);

		$handler = $this->getHandler();
		$handler->onPledgeWasCreated($event);

		$timestamp1 = $streamer->updated_at->timestamp;
		$timestamp2 = $user->updated_at->timestamp;

		$streamer = User::find($streamer->id);
		$user = User::find($user->id);

		$this->assertEquals($timestamp1,$streamer->updated_at->timestamp);
		$this->assertEquals(0,$user->referrals);
		$this->assertEquals($timestamp2,$user->updated_at->timestamp);
		$this->assertEquals(1,$user->start_completed);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(AggregateDataFromPledge::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group handlers
	 *
	 * @covers ::__construct
	 * @covers ::onPledgeWasCreated
	 * @covers ::subscribe
	 *
	 * @uses \App\Models\User
	 * @uses \App\Models\Pledge
	 */
	public function test_on_Pledge_Was_Created()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar',
			'referrals' => 0,
		]);

		$streamer = User::create([
			'email' => 'baz',
			'username' => 'foo',
			'referred_by' => $user->id,
			'referral_completed' => 0
		]);

		$pledge = Pledge::create([
			'user_id'=>$user->id,
			'streamer_id'=>$streamer->id,
			'amount'=>3,
			'type'=>1,
			'message'=>'yeh'
		]);

		$event = new PledgeWasCreated($pledge);

		$handler = $this->getHandler();
		$handler->onPledgeWasCreated($event);

		$timestamp1 = $streamer->updated_at->timestamp;
		$timestamp2 = $user->updated_at->timestamp;

		$streamer = User::find($streamer->id);
		$user = User::find($user->id);

		$this->assertEquals(1,$streamer->referral_completed);
		$this->assertEquals(1,$user->referrals);
		$this->assertEquals(1,$user->start_completed);

		$jobs = DB::table('jobs')->get();

		$this->assertTrue(count($jobs) > 0);

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(AggregateDataFromPledge::class, get_class($command));

		// Now make sure the aggregation from pledge command isn't repeated for some reason!
		
		for ($i = 1; $i < count($jobs)-1; $i++)
		{
			$job = json_decode($jobs[$i]->payload);
			$command = unserialize($job->data->command);

			$this->assertNotEquals(AggregateDataFromPledge::class, get_class($command));
		}
	}

}
