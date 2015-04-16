<?php namespace AppTests\Functional\Commands;

use App\Commands\AggregateDataFromUserUpdate as Command;
use \Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Contracts\Service\Gurus\Aggregation as Guru;

class AggregateDataFromUserUpdateTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	/**
     * @group commands
     * @small
     */
	public function test_handle_with_no_pledge()
	{
		$this->runCommand(999, [], []);

		$this->assertEquals(0,count(DB::table('aggregations')->get()));
	}

	/**
     * @group commands
     * @small
     */
	public function test_handle_with_no_aggregations_for_funds()
	{
		$user = $this->fixtureUser(0);

		$guru = $this->app->make(Guru::class);

		$date = '2014-11-11 11:11:11';
		$this->runCommand($user->id, ['funds'=>10,'updated_at'=>'2013-01-02 03:04:05'], ['funds'=>0,'updated_at'=>(string)$date]);

		$aggregations = DB::table('aggregations')->where('reason',$guru->paidByUser())->where('amount',10)->get();

		$this->assertEquals(5,count($aggregations));

		$aggregations = DB::table('aggregations')->get();

		$this->assertEquals(5,count($aggregations));

		$this->checkAggregationResults($aggregations, $user, new Carbon($date));
	}

	/**
     * @group commands
     * @small
     */
	public function test_handle_with_no_aggregations_for_earnings()
	{
		$user = $this->fixtureUser(0, 10);

		$guru = $this->app->make(Guru::class);

		$date = '2014-11-11 11:11:11';
		$this->runCommand($user->id, ['earnings'=>0,'updated_at'=>'2013-01-02 03:04:05'], ['earnings'=>10,'updated_at'=>(string)$date]);

		$aggregations = DB::table('aggregations')->where('reason',$guru->paidToStreamer())->where('amount',10)->get();

		$this->assertEquals(5,count($aggregations));

		$aggregations = DB::table('aggregations')->get();

		$this->assertEquals(5,count($aggregations));

		$this->checkAggregationResults($aggregations, $user, new Carbon($date));
	}


	/**
     * @group commands
     * @small
     */
	public function test_handle_with_some_aggregations_for_funds()
	{
		$user = $this->fixtureUser(0);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->paidByUser(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 14,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->paidByUser(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation2 = DB::table('aggregations')->orderBy('id','desc')->first();

		$date = '2014-11-11 11:11:11';
		$this->runCommand($user->id, ['funds'=>10,'updated_at'=>'2013-01-02 03:04:05'], ['funds'=>0,'updated_at'=>(string)$date]);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(5,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(10)->get();
		$this->assertEquals(3,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(12,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(13,$aggregation2->amount);

		$this->checkAggregationResults($aggregations, $user, new Carbon($date));
	}

	/**
     * @group commands
     * @small
     */
	public function test_handle_with_some_aggregations_for_earnings()
	{
		$user = $this->fixtureUser(0, 10);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->paidToStreamer(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 14,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->paidToStreamer(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation2 = DB::table('aggregations')->orderBy('id','desc')->first();

		$date = '2014-11-11 11:11:11';
		$this->runCommand($user->id, ['earnings'=>0,'updated_at'=>'2013-01-02 03:04:05'], ['earnings'=>10,'updated_at'=>(string)$date]);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(5,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(10)->get();
		$this->assertEquals(3,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(12,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(13,$aggregation2->amount);

		$this->checkAggregationResults($aggregations, $user, new Carbon($date));
	}

	/**
     * @group commands
     * @small
     */
	public function test_handle_with_some_of_both()
	{
		$user = $this->fixtureUser(0,10);

		$guru = $this->app->make(Guru::class);

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->paidByUser(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 14,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->paidByUser(),
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
			'reason' => $guru->paidToStreamer(),
			'amount' => 4,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 14,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation3 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->paidToStreamer(),
			'amount' => 5,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation4 = DB::table('aggregations')->orderBy('id','desc')->first();

		$date = '2014-11-11 11:11:11';
		$this->runCommand($user->id, ['funds'=>10,'earnings'=>0,'updated_at'=>'2013-01-02 03:04:05'], ['funds'=>0,'earnings'=>10,'updated_at'=>(string)$date]);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(10,count($aggregations));

		$aggregations = DB::table('aggregations')->whereAmount(10)->get();
		$this->assertEquals(6,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(12,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(13,$aggregation2->amount);

		$aggregation3 = DB::table('aggregations')->find($aggregation3->id);
		$this->assertEquals(14,$aggregation3->amount);

		$aggregation4 = DB::table('aggregations')->find($aggregation4->id);
		$this->assertEquals(15,$aggregation4->amount);

		$this->checkAggregationResults($aggregations, $user, new Carbon($date));
	}

	/**
     * @group commands
     * @small
     */
	public function test_handle_with_all_available()
	{
		$user = $this->fixtureUser(0,10);

		$guru = $this->app->make(Guru::class);

		$date = new Carbon('2014-11-11 11:11:11');

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->paidByUser(),
			'amount' => 2,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation = DB::table('aggregations')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->paidByUser(),
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
			'type' => $guru->daily(),
			'reason' => $guru->paidByUser(),
			'amount' => 4,
			'day' => $date->day,
			'week' => 0,
			'month' => $date->month,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation3 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->weekly(),
			'reason' => $guru->paidByUser(),
			'amount' => 5,
			'day' => 0,
			'week' => $date->weekOfYear,
			'month' => 0,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation4 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->monthly(),
			'reason' => $guru->paidByUser(),
			'amount' => 6,
			'day' => 0,
			'week' => 0,
			'month' => $date->month,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation5 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->yearly(),
			'reason' => $guru->paidToStreamer(),
			'amount' => 7,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation6 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->total(),
			'reason' => $guru->paidToStreamer(),
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
			'reason' => $guru->paidToStreamer(),
			'amount' => 9,
			'day' => $date->day,
			'week' => 0,
			'month' => $date->month,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation8 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->weekly(),
			'reason' => $guru->paidToStreamer(),
			'amount' => 10,
			'day' => 0,
			'week' => $date->weekOfYear,
			'month' => 0,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation9 = DB::table('aggregations')->orderBy('id','desc')->first();

		DB::table('aggregations')->insert([
			'user_id' => $user->id,
			'type' => $guru->monthly(),
			'reason' => $guru->paidToStreamer(),
			'amount' => 11,
			'day' => 0,
			'week' => 0,
			'month' => $date->month,
			'year' => $date->format('y'),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$aggregation10 = DB::table('aggregations')->orderBy('id','desc')->first();

		$this->runCommand($user->id, ['funds'=>10,'earnings'=>0,'updated_at'=>'2013-01-02 03:04:05'], ['funds'=>0,'earnings'=>10,'updated_at'=>$date->toDateTimeString()]);

		$aggregations = DB::table('aggregations')->get();
		$this->assertEquals(10,count($aggregations));

		$aggregation = DB::table('aggregations')->find($aggregation->id);
		$this->assertEquals(12,$aggregation->amount);

		$aggregation2 = DB::table('aggregations')->find($aggregation2->id);
		$this->assertEquals(13,$aggregation2->amount);

		$aggregation3 = DB::table('aggregations')->find($aggregation3->id);
		$this->assertEquals(14,$aggregation3->amount);

		$aggregation4 = DB::table('aggregations')->find($aggregation4->id);
		$this->assertEquals(15,$aggregation4->amount);

		$aggregation5 = DB::table('aggregations')->find($aggregation5->id);
		$this->assertEquals(16,$aggregation5->amount);

		$aggregation6 = DB::table('aggregations')->find($aggregation6->id);
		$this->assertEquals(17,$aggregation6->amount);

		$aggregation7 = DB::table('aggregations')->find($aggregation7->id);
		$this->assertEquals(18,$aggregation7->amount);

		$aggregation8 = DB::table('aggregations')->find($aggregation8->id);
		$this->assertEquals(19,$aggregation8->amount);

		$aggregation9 = DB::table('aggregations')->find($aggregation9->id);
		$this->assertEquals(20,$aggregation9->amount);

		$aggregation10 = DB::table('aggregations')->find($aggregation10->id);
		$this->assertEquals(21,$aggregation10->amount);

		$this->checkAggregationResults($aggregations, $user, $date);
	}

	private function runCommand($userId, array $changed, array $current)
	{
		$command = new Command($userId, $changed, $current);

		$this->app->instance(Command::class, $command);

		$this->app->call(Command::class.'@handle');
	}

	private function fixtureUser($funds, $earnings = 0)
	{
		DB::table('users')->insert([
			'email' => 'foo@bar.com'.$earnings,
			'earnings' => $earnings,
			'funds' => $funds,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		return DB::table('users')->first();
	}

	private function checkAggregationResults($aggregations, $user, $date)
	{
		$guru = $this->app->make(Guru::class);

		foreach ($aggregations as $aggregation)
		{
			$this->assertEquals($user->id,$aggregation->user_id);

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
