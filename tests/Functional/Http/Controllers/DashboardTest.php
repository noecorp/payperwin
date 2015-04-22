<?php namespace AppTests\Functional\Http\Controllers;

use Mockery as m;
use Illuminate\Support\Facades\DB;
use App\Contracts\Service\Gurus\Pledge as PledgeGuru;
use App\Contracts\Service\Gurus\Aggregation as AggregationGuru;

/**
 * @coversDefaultClass \App\Http\Controllers\Dashboard
 */
class DashboardTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 *
	 * @uses \App\Models\User
	 */
	public function test_index_redirects_brand_new_user()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar'
		]);
		$this->become($user->id);

		$response = $this->call('GET', 'dashboard');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('start'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 *
	 * @uses \App\Models\User
	 */
	public function test_index_redirects_if_not_logged_in()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar'
		]);

		$response = $this->call('GET', 'dashboard');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::index
	 * @covers ::pledgesCreated
	 * @covers ::earningsSummary
	 * @covers ::dailyAmounts
	 * @covers ::pledgeStats
	 * @covers ::latestPledges
	 * @covers ::leaderboard
	 * @covers ::countSpenders
	 *
	 * @uses \App\Models\User
	 */
	public function test_index_ok_for_completed_blank_user()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => true
		]);
		$this->become($user->id);

		$response = $this->call('GET', 'dashboard');

		$this->assertResponseOk();
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::index
	 * @covers ::pledgesCreated
	 * @covers ::earningsSummary
	 * @covers ::dailyAmounts
	 * @covers ::pledgeStats
	 * @covers ::latestPledges
	 * @covers ::leaderboard
	 * @covers ::countSpenders
	 *
	 * @uses \App\Models\User
	 * @uses \App\Services\Gurus\Pledge
	 * @uses \App\Services\Gurus\Aggregation
	 */
	public function test_index_ok_for_streamer()
	{
		$pledgeGuru = $this->app->make(PledgeGuru::class);
		$aggregationGuru = $this->app->make(AggregationGuru::class);

		$streamer = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => true,
			'streamer_completed' => true,
			'twitch_id' => 1,
			'twitch_username' => 'bar',
			'summoner_id' => 2,
			'summoner_name' => 'baz',
			'region' => 'na',
			'earnings' => 999
		]);
		$this->become($streamer->id);

		$user = $this->fixture('users', [
			'email' => 'bar',
			'username' => 'foo'
		]);

		$pledge = $this->fixture('pledges', [
			'amount' => 1,
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'type' => $pledgeGuru->win(),
			'message' => 'hi'
		]);

		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->total(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 777,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
		]);
		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->monthly(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->yearly(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 5,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->daily(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 7,
			'day' => $this->carbon->day,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);

		$response = $this->call('GET', 'dashboard');

		$this->assertResponseOk();
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::index
	 * @covers ::pledgesCreated
	 * @covers ::earningsSummary
	 * @covers ::dailyAmounts
	 * @covers ::pledgeStats
	 * @covers ::latestPledges
	 * @covers ::leaderboard
	 * @covers ::countSpenders
	 *
	 * @uses \App\Models\User
	 * @uses \App\Services\Gurus\Pledge
	 * @uses \App\Services\Gurus\Aggregation
	 */
	public function test_index_ok_for_user()
	{
		$pledgeGuru = $this->app->make(PledgeGuru::class);
		$aggregationGuru = $this->app->make(AggregationGuru::class);

		$streamer = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => true,
			'streamer_completed' => true,
			'twitch_id' => 1,
			'twitch_username' => 'bar',
			'summoner_id' => 2,
			'summoner_name' => 'baz',
			'region' => 'na',
			'earnings' => 999
		]);

		$user = $this->fixture('users', [
			'email' => 'bar',
			'username' => 'foo',
			'start_completed' => true,
			'funds' => 100
		]);
		$this->become($user->id);

		$pledge = $this->fixture('pledges', [
			'amount' => 1,
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'type' => $pledgeGuru->win(),
			'message' => 'hi'
		]);

		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->total(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 777,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
		]);
		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->monthly(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->yearly(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 5,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->daily(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 7,
			'day' => $this->carbon->day,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);

		$response = $this->call('GET', 'dashboard');

		$this->assertResponseOk();
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::index
	 * @covers ::pledgesCreated
	 * @covers ::earningsSummary
	 * @covers ::dailyAmounts
	 * @covers ::pledgeStats
	 * @covers ::latestPledges
	 * @covers ::leaderboard
	 * @covers ::countSpenders
	 *
	 * @uses \App\Models\User
	 * @uses \App\Services\Gurus\Pledge
	 * @uses \App\Services\Gurus\Aggregation
	 */
	public function test_index_ok_for_both()
	{
		$pledgeGuru = $this->app->make(PledgeGuru::class);
		$aggregationGuru = $this->app->make(AggregationGuru::class);

		$streamer = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => true,
			'streamer_completed' => true,
			'twitch_id' => 1,
			'twitch_username' => 'bar',
			'summoner_id' => 2,
			'summoner_name' => 'baz',
			'region' => 'na',
			'earnings' => 999
		]);
		$this->become($streamer->id);

		$user = $this->fixture('users', [
			'email' => 'bar',
			'username' => 'foo'
		]);

		$pledge = $this->fixture('pledges', [
			'amount' => 1,
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'type' => $pledgeGuru->win(),
			'message' => 'hi'
		]);

		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->total(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 777,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
		]);
		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->monthly(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->yearly(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 5,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $streamer->id,
			'type' => $aggregationGuru->daily(),
			'reason' => $aggregationGuru->paidToStreamer(),
			'amount' => 7,
			'day' => $this->carbon->day,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);

		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->total(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 777,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
		]);
		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->monthly(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 3,
			'day' => 0,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->yearly(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 5,
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => $this->carbon->format('y'),
		]);
		$this->fixture('aggregations', [
			'user_id' => $user->id,
			'type' => $aggregationGuru->daily(),
			'reason' => $aggregationGuru->paidByUser(),
			'amount' => 7,
			'day' => $this->carbon->day,
			'week' => 0,
			'month' => $this->carbon->month,
			'year' => $this->carbon->format('y'),
		]);

		$response = $this->call('GET', 'dashboard');

		$this->assertResponseOk();
	}

}
