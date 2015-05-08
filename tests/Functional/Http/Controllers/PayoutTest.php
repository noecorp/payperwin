<?php namespace AppTests\Functional\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Services\Gurus\Transaction as Guru;

/**
 * @coversDefaultClass \App\Http\Controllers\Payout
 */
class PayoutTest extends \AppTests\TestCase {

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
	public function test_index_redirects_when_not_logged_in()
	{
		$this->call('GET', 'payout');

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
	 */
	public function test_index_redirects_when_not_started()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => false
		]);
		$this->become($user->id);

		$this->call('GET', 'payout');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('start'));
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
	 */
	public function test_index_redirects_when_not_streamer()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => true,
			'streamer' => false
		]);
		$this->become($user->id);

		$this->call('GET', 'payout');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('dashboard'));
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
	 */
	public function test_index_ok()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => true,
			'streamer' => true,
		]);
		$this->become($user->id);

		$this->call('GET', 'payout');

		$this->assertResponseOk();
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::postIndex
	 */
	public function test_post_redirects_when_not_logged_in()
	{
		$this->session(['_token'=>'foo']);

		$this->call('POST', 'payout', ['_token' => 'foo']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::postIndex
	 *
	 * @uses \App\Models\User
	 */
	public function test_post_redirects_when_not_started()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar',
			'start_completed' => false
		]);
		$this->become($user->id);

		$this->session(['_token'=>'foo']);

		$this->call('POST', 'payout', ['_token' => 'foo']);

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('start'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::postIndex
	 *
	 * @uses \App\Models\User
	 */
	public function test_post_with_invalid_form_data()
	{
		$user = $this->fixture('users', [
			'email' => 'foo@bar.com',
			'username' => 'bar',
			'start_completed' => true,
			'streamer' => true,
			'earnings' => 99
		]);
		$this->become($user->id);

		$this->session(['_token'=>'foo']);

		$this->call('POST', 'payout', ['_token' => 'foo', 'amount' => 100, 'email' => 'foo'], [], [], ['HTTP_REFERER' => app_url('baz')]);

		$this->assertHasOldInput();
		$this->assertRedirectedTo(app_url('baz'));
		$this->assertSessionHasErrors(['email','amount']);
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::postIndex
	 *
	 * @uses \App\Models\User
	 * @uses \App\Services\Gurus\Transaction
	 */
	public function test_post_ok()
	{
		$user = $this->fixture('users', [
			'email' => 'foo@bar.com',
			'username' => 'bar',
			'start_completed' => true,
			'streamer' => true,
			'earnings' => 101,
			'commission' => 3.3
		]);
		$this->become($user->id);

		$net = (floatval(100) * (1 - 2.9 / 100) - 0.30) * (1 - 3.3 / 100);

		$this->session(['_token'=>'foo']);

		$this->call('POST', 'payout', ['_token' => 'foo', 'amount' => 100, 'email' => 'foo2@bar.com'], [], [], ['HTTP_REFERER' => app_url('baz')]);

		$this->assertSessionHas('success');
		$this->assertRedirectedTo(app_url('baz'));

		$logs = $this->getLog();

		$this->assertContains(view('emails.admin.payout',['username'=>'bar','email'=>'foo2@bar.com','amount' => 100, 'net' => $net])->render(), $logs);

		$user = DB::table('users')->first();

		$this->assertEquals(1, $user->earnings);

		$this->assertEquals(1, DB::table('transactions')->count());

		$guru = new Guru();

		$transaction = DB::table('transactions')->first();

		$this->assertEquals($user->id, $transaction->user_id);
		$this->assertEquals(100, $transaction->amount);
		$this->assertEquals($guru->streamerPaidOut(), $transaction->transaction_type);
	}

}
