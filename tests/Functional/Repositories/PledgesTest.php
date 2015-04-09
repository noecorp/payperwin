<?php namespace AppTests\Functional\Repositories;

use Mockery as m;
use App\Repositories\Pledges;

class PledgesTest extends \AppTests\TestCase {

	private function getRepo()
	{
		return new Pledges($this->app);
	}

	public function testCreateSuccessful()
	{
		$repo = $this->getRepo();

		// First we check that the pledge ended up in the database successfully

		$pledge = $repo->create([
			'user_id' => 1,
			'amount' => 1,
			'type' => 1,
			'streamer_id' => 1,
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
