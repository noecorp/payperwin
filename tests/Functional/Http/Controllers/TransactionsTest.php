<?php namespace AppTests\Functional\Http\Controllers;

use Mockery as m;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Contracts\Service\Gurus\Transaction as TransactionGuru;
use App\Contracts\Service\Gurus\Pledge as PledgeGuru;
use App\Models\Transaction;

/**
 * @coversDefaultClass \App\Http\Controllers\Transactions
 */
class TransactionsTest extends \AppTests\TestCase {

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
	 * @covers ::getIndex
	 */
	public function test_index_requires_auth()
	{
		$response = $this->call('GET','transactions');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::getIndex
	 *
	 * @uses \App\Models\User
	 * @uses \App\Models\Transaction
	 * @uses \App\Services\Gurus\Transactions
	 * @uses \App\Services\Gurus\Pledges
	 */
	public function test_index_ok()
	{
		DB::table('users')->insert(
		[
			'email'=>'foo',
			'username'=>'bar',
			'streamer' => 0,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$user = User::first();

		$this->be($user);

		DB::table('users')->insert(
		[
			'email'=>'bar',
			'username'=>'foo',
			'streamer' => 1,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$streamer = DB::table('users')->where('id','!=',$user->id)->first();

		$pledgesGuru = $this->app->make(PledgeGuru::class);

		DB::table('pledges')->insert(
		[
			'amount' => 9.99,
			'type' => $pledgesGuru->win(),
			'message' => 'foo',
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);
		$pledge = DB::table('pledges')->first();

		$transactionsGuru = $this->app->make(TransactionGuru::class);

		for ($i = 1; $i <= 11; $i++)
		{
			DB::table('transactions')->insert(
			[
				'user_id' => $user->id,
				'transaction_type' => $transactionsGuru->pledgeTaken(),
				'pledge_id' => $pledge->id,
				'amount' => '9.99',
				'source' => 0,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]);
		}

		$transaction = DB::table('transactions')->orderBy('id','desc')->first();

		for ($i = 1; $i <= 11; $i++)
		{
			DB::table('transactions')->insert(
			[
				'user_id' => $streamer->id,
				'transaction_type' => $transactionsGuru->pledgePaid(),
				'pledge_id' => $pledge->id,
				'amount' => '9.99',
				'source' => 0,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]);
		}

		$response = $this->call('GET','transactions');
		
		$this->assertResponseOk();
		$this->assertViewHasAll(['transactions','guru','page','more','less']);

		$transactions = $this->viewData('transactions');

		$filtered = $transactions->filter(function(Transaction $t) use ($transaction)
		{
			return ($transaction->id === $t->id);
		});

		$this->assertEquals(0, $filtered->count());

		// Now check page 2
		$response = $this->call('GET','transactions?page=2');
		
		$this->assertResponseOk();
		$this->assertViewHasAll(['transactions','guru','page','more','less']);

		$transactions = $this->viewData('transactions');

		$this->assertEquals(1, $transactions->count());
		$this->assertEquals($transaction->id, $transactions->first()->id);
	}

}
