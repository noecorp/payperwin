<?php namespace AppTests\Functional\Repositories;

use Mockery as m;
use App\Repositories\Pledges;
use App\Models\User;

class PledgesTest extends \AppTests\TestCase {

	private function getRepo()
	{
		return new Pledges($this->app);
	}

	public function testCreateSuccessful()
	{
		$user = User::create([
			'email' => 'foo',
			'username' => 'bar'
		]);

		$streamer = User::create([
			'email' => 'bar',
			'username' => 'foo',
			'streamer' => 1,
			'streamer_completed' => 1,
			'twitch_id' => 1,
			'twitch_username' => 'foo',
			'summoner_id' => 1,
			'summoner_name' => 'foo'
		]);

		$repo = $this->getRepo();

		// First we check that the pledge ended up in the database successfully

		$pledge = $repo->create([
			'user_id' => $user->id,
			'amount' => 1,
			'type' => 1,
			'streamer_id' => $streamer->id,
			'message' => 'foo',
		]);

		$this->assertTrue($pledge->exists);

		$latest = $repo->find($pledge->id);

		$this->assertEquals($pledge->id,$latest->id);
		$this->assertEquals($pledge->created_at,$latest->created_at);

		// Next we check that nothing actually ended up in the database after a failed pledge creation

		$this->setExpectedException('InvalidArgumentException');

		$pledge = $repo->create([
			'user_id' => 1,
			'amount' => 1,
			'type' => 1,
			'streamer_id' => 1,
			'end_date' => 'foo'
		]);

		$this->assertEquals($repo->all()->count(),1);
	}

}
