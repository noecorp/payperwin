<?php namespace AppTests\Functional\Controllers;

use App\Models\User;
use App\Models\Pledge;
use Illuminate\Database\Eloquent\Collection;

class UsersPledgesTest extends \AppTests\TestCase {

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

	public function testIndexAbortsWhenNotFound()
	{
		$response = $this->call('GET','users/foo/pledges');

		$this->assertResponseStatus(404);
	}

}
