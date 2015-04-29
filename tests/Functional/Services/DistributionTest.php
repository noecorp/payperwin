<?php namespace AppTests\Functional\Services;

use Mockery as m;
use Illuminate\Support\Facades\DB;

use App\Services\Distribution;

use App\Contracts\Repository\Users;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Matches;
use App\Contracts\Repository\Transactions;
use App\Contracts\Service\Gurus\Transaction as TransactionGuru;
use App\Contracts\Service\Acidifier;

/**
 * @coversDefaultClass \App\Services\Distribution
 */
class DistributionTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	private function getService()
	{
		return new Distribution(
			$this->app->make(Users::class),
			$this->app->make(Matches::class),
			$this->app->make(Pledges::class),
			$this->app->make(Transactions::class),
			$this->app->make(Acidifier::class),
			$this->app->make(TransactionGuru::class)
		);
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::pledgesFor
	 */
	public function test_with_no_pledges()
	{
		$distribute = $this->getService();

		$distribute->pledgesFor(999);

		$this->assertEquals(0, DB::table('transactions')->count());
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::pledgesFor
	 * @covers ::stopExpired
	 */
	public function test_with_only_expired()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'funds' => 100
		]);
		
		$streamer = $this->fixture('users', [
			'email' => 'bar',
			'username' => 'foo',
			'streamer' => 1
		]);

		$pledge1 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 10,
			'win_limit' => 1,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 1'
		]);

		$pledge2 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 10,
			'spending_limit' => 8,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 2'
		]);

		$pledge3 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 10,
			'end_date' => $this->carbon,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 3'
		]);

		$distribute = $this->getService();

		$distribute->pledgesFor($streamer->id);

		$this->assertEquals(3, DB::table('pledges')->count());
		$this->assertEquals(3, DB::table('pledges')->where('running',0)->count());
		$this->assertEquals(3, DB::table('pledges')->where('times_donated',1)->count());

		$this->assertEquals(0, DB::table('transactions')->count());

		$this->assertEquals(100, DB::table('users')->where('id', $user->id)->select('funds')->first()->funds);		
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::pledgesFor
	 * @covers ::stopExpired
	 * @covers ::processRunning
	 */
	public function test_with_expired_and_no_streamer()
	{
		$pledge1 = $this->fixture('pledges', [
			'user_id' => 1,
			'streamer_id' => 2,
			'amount' => 10,
			'win_limit' => 1,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 1'
		]);

		$pledge2 = $this->fixture('pledges', [
			'user_id' => 1,
			'streamer_id' => 2,
			'amount' => 10,
			'spending_limit' => 8,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 2'
		]);

		$pledge3 = $this->fixture('pledges', [
			'user_id' => 1,
			'streamer_id' => 2,
			'amount' => 10,
			'end_date' => $this->carbon,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 3'
		]);

		$distribute = $this->getService();

		$distribute->pledgesFor(2);

		$this->assertEquals(3, DB::table('pledges')->count());
		$this->assertEquals(3, DB::table('pledges')->where('running',0)->count());
		$this->assertEquals(3, DB::table('pledges')->where('times_donated',1)->count());

		$this->assertEquals(0, DB::table('transactions')->count());
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::pledgesFor
	 * @covers ::stopExpired
	 * @covers ::processRunning
	 *
	 * @uses \App\Services\Gurus\Transaction
	 */
	public function test_with_only_running()
	{
		$user1Funds = 100;
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'funds' => $user1Funds
		]);

		$user2Funds = 90;
		$user2 = $this->fixture('users', [
			'email' => 'foo2',
			'username' => 'bar2',
			'funds' => $user2Funds
		]);

		$user3Funds = 9;
		$user3 = $this->fixture('users', [
			'email' => 'foo3',
			'username' => 'bar3',
			'funds' => $user3Funds
		]);
		
		$streamer = $this->fixture('users', [
			'email' => 'bar',
			'username' => 'foo',
			'streamer' => 1,
			'earnings' => 0
		]);

		$match1 = $this->fixture('matches', [
			'server_match_id' => 1111111,
			'user_id' => $streamer->id,
			'win' => 1,
			'champion' => 1,
			'kills' => 7,
			'deaths' => 0,
			'assists' => 5,
			'match_date' => $this->carbon,
			'settled' => 0,
		]);

		$match2 = $this->fixture('matches', [
			'server_match_id' => 1111112,
			'user_id' => $streamer->id,
			'win' => 0,
			'champion' => 1,
			'kills' => 7,
			'deaths' => 0,
			'assists' => 5,
			'match_date' => $this->carbon,
			'settled' => 0,
		]);

		// valid, not expiring
		$pledge1 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 3.33,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 1'
		]);

		// valid, not expiring
		$pledge15 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 1.11,
			'spending_limit' => 4.44,
			'times_donated' => 0,
			'running' => 1,
			'type' => 1,
			'message' => 'message 15'
		]);

		// valid, expiring spending
		$pledge2 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 5,
			'spending_limit' => 10,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 2'
		]);

		// valid, expiring wins
		$pledge4 = $this->fixture('pledges', [
			'user_id' => $user2->id,
			'streamer_id' => $streamer->id,
			'amount' => 7,
			'win_limit' => 4,
			'times_donated' => 3,
			'running' => 1,
			'type' => 1,
			'message' => 'message 4'
		]);

		// valid, not enough funds
		$pledge5 = $this->fixture('pledges', [
			'user_id' => $user3->id,
			'streamer_id' => $streamer->id,
			'amount' => 10,
			'times_donated' => 0,
			'running' => 1,
			'type' => 1,
			'message' => 'message 5'
		]);

		$distribute = $this->getService();

		$distribute->pledgesFor($streamer->id);

		$user = DB::table('users')->find($user->id);
		$this->assertEquals($user1Funds - 9.44, $user->funds);

		$user2 = DB::table('users')->find($user2->id);
		$this->assertEquals($user2Funds - 7, $user2->funds);

		$user3 = DB::table('users')->find($user3->id);
		$this->assertEquals($user3Funds, $user3->funds);

		$streamer = DB::table('users')->find($streamer->id);
		$this->assertEquals(16.44, $streamer->earnings);

		$pledge1 = DB::table('pledges')->find($pledge1->id);
		$this->assertEquals(1, $pledge1->running);
		$this->assertEquals(2, $pledge1->times_donated);

		$pledge15 = DB::table('pledges')->find($pledge15->id);
		$this->assertEquals(1, $pledge15->running);
		$this->assertEquals(1, $pledge15->times_donated);

		$pledge2 = DB::table('pledges')->find($pledge2->id);
		$this->assertEquals(0, $pledge2->running);
		$this->assertEquals(2, $pledge2->times_donated);

		$pledge4 = DB::table('pledges')->find($pledge4->id);
		$this->assertEquals(0, $pledge4->running);
		$this->assertEquals(4, $pledge4->times_donated);

		$pledge5 = DB::table('pledges')->find($pledge5->id);
		$this->assertEquals(1, $pledge5->running);
		$this->assertEquals(0, $pledge5->times_donated);

		$guru = $this->app->make(TransactionGuru::class);

		$this->assertEquals(8, DB::table('transactions')->count());

		$this->assertEquals(1, DB::table('transactions')->where('user_id',$user->id)
			->where('amount',$pledge1->amount)
			->where('pledge_id',$pledge1->id)
			->where('transaction_type',$guru->pledgeTaken())
			->where('username', $streamer->username)
			->count());
		$this->assertEquals(1, DB::table('transactions')->where('user_id',$streamer->id)
			->where('amount',$pledge1->amount)
			->where('pledge_id',$pledge1->id)
			->where('transaction_type',$guru->pledgePaid())
			->where('username', $user->username)
			->count());

		$this->assertEquals(1, DB::table('transactions')->where('user_id',$user->id)
			->where('amount',$pledge15->amount)
			->where('pledge_id',$pledge15->id)
			->where('transaction_type',$guru->pledgeTaken())
			->where('username', $streamer->username)
			->count());
		$this->assertEquals(1, DB::table('transactions')->where('user_id',$streamer->id)
			->where('amount',$pledge15->amount)
			->where('pledge_id',$pledge15->id)
			->where('transaction_type',$guru->pledgePaid())
			->where('username', $user->username)
			->count());

		$this->assertEquals(1, DB::table('transactions')->where('user_id',$user->id)
			->where('amount',$pledge2->amount)
			->where('pledge_id',$pledge2->id)
			->where('transaction_type',$guru->pledgeTaken())
			->where('username', $streamer->username)
			->count());
		$this->assertEquals(1, DB::table('transactions')->where('user_id',$streamer->id)
			->where('amount',$pledge2->amount)
			->where('pledge_id',$pledge2->id)
			->where('transaction_type',$guru->pledgePaid())
			->where('username', $user->username)
			->count());

		$this->assertEquals(1, DB::table('transactions')->where('user_id',$user2->id)
			->where('amount',$pledge4->amount)
			->where('pledge_id',$pledge4->id)
			->where('transaction_type',$guru->pledgeTaken())
			->where('username', $streamer->username)
			->count());
		$this->assertEquals(1, DB::table('transactions')->where('user_id',$streamer->id)
			->where('amount',$pledge4->amount)
			->where('pledge_id',$pledge4->id)
			->where('transaction_type',$guru->pledgePaid())
			->where('username', $user2->username)
			->count());


		// Re-running should have no changes at all!


		$distribute->pledgesFor($streamer->id);

		$user = DB::table('users')->find($user->id);
		$this->assertEquals($user1Funds - 9.44, $user->funds);

		$user2 = DB::table('users')->find($user2->id);
		$this->assertEquals($user2Funds - 7, $user2->funds);

		$user3 = DB::table('users')->find($user3->id);
		$this->assertEquals($user3Funds, $user3->funds);

		$streamer = DB::table('users')->find($streamer->id);
		$this->assertEquals(16.44, $streamer->earnings);

		$this->assertEquals(3, DB::table('pledges')->where('running',1)->count());

		$this->assertEquals(8, DB::table('transactions')->count());
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::pledgesFor
	 * @covers ::stopExpired
	 * @covers ::processRunning
	 */
	public function test_with_both()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'funds' => 100
		]);
		
		$streamer = $this->fixture('users', [
			'email' => 'bar',
			'username' => 'foo',
			'streamer' => 1
		]);

		$match = $this->fixture('matches', [
			'server_match_id' => 1111111,
			'user_id' => $streamer->id,
			'win' => 1,
			'champion' => 1,
			'kills' => 7,
			'deaths' => 0,
			'assists' => 5,
			'match_date' => $this->carbon,
			'settled' => 0,
		]);

		$pledge1 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 3,
			'win_limit' => 3,
			'times_donated' => 1,
			'running' => 1,
			'type' => 1,
			'message' => 'message 1'
		]);

		$pledge2 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 10,
			'spending_limit' => 10,
			'times_donated' => 0,
			'running' => 1,
			'type' => 1,
			'message' => 'message 2'
		]);

		$pledge3 = $this->fixture('pledges', [
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 10,
			'end_date' => $this->carbon,
			'times_donated' => 3,
			'running' => 1,
			'type' => 1,
			'message' => 'message 3'
		]);

		$distribute = $this->getService();

		$distribute->pledgesFor($streamer->id);

		$this->assertEquals(3, DB::table('pledges')->count());

		$this->assertEquals(1, DB::table('matches')->where('settled',1)->count());

		$this->assertEquals(1, DB::table('pledges')->where('running',1)->count());
		$this->assertEquals(1, DB::table('pledges')->where('times_donated',3)->count());
		$this->assertEquals(1, DB::table('pledges')->where('times_donated',2)->count());
		$this->assertEquals(1, DB::table('pledges')->where('times_donated',1)->count());

		$this->assertEquals(4, DB::table('transactions')->count());
	}

}
