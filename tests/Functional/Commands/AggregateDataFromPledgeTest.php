<?php namespace AppTests\Functional\Commands;

use App\Commands\AggregateDataFromPledge as Command;
use \Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Contracts\Service\Gurus\Aggregation as Guru;

/**
 * @coversDefaultClass \App\Commands\AggregateDataFromPledge
 */
class AggregateDataFromPledgeTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 */
	public function test_handle_with_no_pledge()
	{
		$this->runCommand(999);

		$this->assertEquals(0,count(DB::table('aggregations')->get()));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_no_aggregations()
	{
		$user = $this->fixtureUser();
		$streamer = $this->fixtureStreamer();
		$pledge = $this->fixturePledge($user, $streamer);

		$this->runCommand($pledge->id);

		$aggregations = DB::table('aggregations')->where('amount',1)->get();

		$this->assertEquals(10,count($aggregations));

		$this->checkAggregationResults($aggregations, $user, $streamer, new Carbon($pledge->created_at));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_some_user_aggregations()
	{
		$user = $this->fixtureUser();
		$streamer = $this->fixtureStreamer();
		$pledge = $this->fixturePledge($user, $streamer);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation2 = DB::table('aggregations')->orderBy('id','desc')->first();

		$this->runCommand($pledge->id);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(10,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(1)->get();
		$this->assertEquals(8,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(3,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(4,$aggregation2->amount);

		$this->checkAggregationResults($aggregations, $user, $streamer, new Carbon($pledge->created_at));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_some_streamer_aggregations()
	{
		$user = $this->fixtureUser();
		$streamer = $this->fixtureStreamer();
		$pledge = $this->fixturePledge($user, $streamer);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->yearly(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->total(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation2 = DB::table('aggregations')->orderBy('id','desc')->first();

		$this->runCommand($pledge->id);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(10,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(1)->get();
		$this->assertEquals(8,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(3,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(4,$aggregation2->amount);

		$this->checkAggregationResults($aggregations, $user, $streamer, new Carbon($pledge->created_at));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_some_of_both()
	{
		$user = $this->fixtureUser();
		$streamer = $this->fixtureStreamer();
		$pledge = $this->fixturePledge($user, $streamer);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->yearly(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->total(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation2 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 4,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation3 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 5,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation4 = DB::table('aggregations')->orderBy('id','desc')->first();

		$this->runCommand($pledge->id);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(10,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(1)->get();
		$this->assertEquals(6,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(3,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(4,$aggregation2->amount);

		$aggregation3 = DB::table('aggregations')->find($aggregation3->id);
		$this->assertEquals(5,$aggregation3->amount);

		$aggregation4 = DB::table('aggregations')->find($aggregation4->id);
		$this->assertEquals(6,$aggregation4->amount);

		$this->checkAggregationResults($aggregations, $user, $streamer, new Carbon($pledge->created_at));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_all_available()
	{
		$user = $this->fixtureUser();
		$streamer = $this->fixtureStreamer();
		$pledge = $this->fixturePledge($user, $streamer);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->yearly(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->total(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation2 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->daily(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 4,
			'day' => 1,
			'week' => 0,
			'month' => 1,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation3 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->weekly(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 5,
			'day' => 0,
			'week' => 1,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation4 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $streamer->id,
			'type' => $guru->monthly(),
			'reason' => $guru->pledgeToStreamer(),
			'amount' => 6,
			'day' => 0,
			'week' => 0,
			'month' => 1,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation5 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 7,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation6 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 8,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation7 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->daily(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 9,
			'day' => 1,
			'week' => 0,
			'month' => 1,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation8 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->weekly(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 10,
			'day' => 0,
			'week' => 1,
			'month' => 0,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation9 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->monthly(),
			'reason' => $guru->pledgeFromUser(),
			'amount' => 11,
			'day' => 0,
			'week' => 0,
			'month' => 1,
			'year' => 15,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation10 = DB::table('aggregations')->orderBy('id','desc')->first();

		$this->runCommand($pledge->id);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(10,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(1)->get();
		$this->assertEquals(0,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(3,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(4,$aggregation2->amount);

		$aggregation3 = DB::table('aggregations')->find($aggregation3->id);
		$this->assertEquals(5,$aggregation3->amount);

		$aggregation4 = DB::table('aggregations')->find($aggregation4->id);
		$this->assertEquals(6,$aggregation4->amount);

		$aggregation5 = DB::table('aggregations')->find($aggregation5->id);
		$this->assertEquals(7,$aggregation5->amount);

		$aggregation6 = DB::table('aggregations')->find($aggregation6->id);
		$this->assertEquals(8,$aggregation6->amount);

		$aggregation7 = DB::table('aggregations')->find($aggregation7->id);
		$this->assertEquals(9,$aggregation7->amount);

		$aggregation8 = DB::table('aggregations')->find($aggregation8->id);
		$this->assertEquals(10,$aggregation8->amount);

		$aggregation9 = DB::table('aggregations')->find($aggregation9->id);
		$this->assertEquals(11,$aggregation9->amount);

		$aggregation10 = DB::table('aggregations')->find($aggregation10->id);
		$this->assertEquals(12,$aggregation10->amount);

		$this->checkAggregationResults($aggregations, $user, $streamer, new Carbon($pledge->created_at));
	}

	private function runCommand($pledgeId)
	{
		$command = new Command($pledgeId);

		$this->app->instance(Command::class, $command);

		$this->app->call(Command::class.'@handle');
	}

	private function fixtureUser()
	{
		DB::table('users')->insert([
			'email' => 'foo@bar.com',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		return DB::table('users')->first();
	}

	private function fixtureStreamer()
	{
		DB::table('users')->insert([
			'email' => 'bar@foo.com',
			'twitch_id' => 999,
			'twitch_username' => 'bar',
			'summoner_id' => 777,
			'summoner_name' => 'baz',
			'region' => 'na',
			'streamer' => 1,
			'streamer_completed' => 1,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		return DB::table('users')->where('streamer',1)->first();
	}

	private function fixturePledge($user, $streamer)
	{
		$date = new Carbon('2015-01-02 03:04:05');
		DB::table('pledges')->insert([
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 1,
			'type' => 1,
			'message' => 'hi',
			'created_at' => $date,
			'updated_at' => $date,
		]);
		return DB::table('pledges')->first();
	}

	private function checkAggregationResults($aggregations, $user, $streamer, $date)
	{
		$guru = $this->app->make(Guru::class);

		foreach ($aggregations as $aggregation)
		{
			if ($aggregation->reason == $guru->pledgeToStreamer())
			{
				$this->assertEquals($streamer->id,$aggregation->user_id);
			}
			else if ($aggregation->reason == $guru->pledgeFromUser())
			{
				$this->assertEquals($user->id,$aggregation->user_id);
			}
			else
			{
				$this->assertTrue(false);
			}

			switch ($aggregation->type) {
				case $guru->daily():
					$this->assertEquals($date->day, $aggregation->day);
					$this->assertEquals(0, $aggregation->week);
					$this->assertEquals($date->month, $aggregation->month);
					$this->assertEquals((int)$date->format('y'), $aggregation->year);
					break;
				case $guru->weekly():
					$this->assertEquals(0, $aggregation->day);
					$this->assertEquals($date->weekOfYear, $aggregation->week);
					$this->assertEquals(0, $aggregation->month);
					$this->assertEquals((int)$date->format('y'), $aggregation->year);
					break;
				case $guru->monthly():
					$this->assertEquals(0, $aggregation->day);
					$this->assertEquals(0, $aggregation->week);
					$this->assertEquals($date->month, $aggregation->month);
					$this->assertEquals((int)$date->format('y'), $aggregation->year);
					break;
				case $guru->yearly():
					$this->assertEquals(0, $aggregation->day);
					$this->assertEquals(0, $aggregation->week);
					$this->assertEquals(0, $aggregation->month);
					$this->assertEquals((int)$date->format('y'), $aggregation->year);
					break;
				case $guru->total():
					$this->assertEquals(0, $aggregation->day);
					$this->assertEquals(0, $aggregation->week);
					$this->assertEquals(0, $aggregation->month);
					$this->assertEquals(0, $aggregation->year);
					break;
				default:
					$this->assertTrue(false);
					break;
			}
		}
	}

}
