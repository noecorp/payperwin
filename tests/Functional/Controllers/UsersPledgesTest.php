<?php namespace AppTests\Functional\Controllers;

use App\Models\User;
use App\Models\Pledge;
use Illuminate\Database\Eloquent\Collection;

/**
 * @coversDefaultClass \App\Http\Controllers\UsersPledges
 */
class UsersPledgesTest extends \AppTests\TestCase {

	/**
     * {@inheritdoc}
     */
    protected $migrate = true;

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::index
	 */
	public function testIndexOkWithNoPledges()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$response = $this->call('GET','users/1/pledges');

		$this->assertResponseOk();

		$pledges = $this->viewData('pledges');

		$this->assertTrue($pledges instanceof Collection);
		$this->assertTrue($pledges->isEmpty());
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::index
	 */
	public function testIndexOkWithPledges()
	{
		$streamer = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$user = User::create([
			'email' => 'bar@foo.com',
			'username' => 'bar',
		]);

		Pledge::create([
			'user_id' => $user->id,
			'streamer_id' => $streamer->id,
			'amount' => 1,
			'type' => 1,
			'message' => 'hi',
		]);

		$response = $this->call('GET','users/2/pledges');

		$this->assertResponseOk();

		$pledges = $this->viewData('pledges');

		$this->assertTrue($pledges instanceof Collection);
		$this->assertFalse($pledges->isEmpty());
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::index
	 */
	public function testIndexAbortsWhenNotFound()
	{
		$response = $this->call('GET','users/foo/pledges');

		$this->assertResponseStatus(404);
	}

}
