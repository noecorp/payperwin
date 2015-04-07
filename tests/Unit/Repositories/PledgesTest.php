<?php namespace AppTests\Unit\Repositories;

use Mockery as m;
use App\Models\Pledge;
use App\Repositories\Pledges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Connection;

class PledgesTest extends \AppTests\TestCase {

	private function getRepo()
	{
		return new Pledges($this->app);
	}

	private function getModelMock()
	{
		return m::mock(Pledge::class);
	}

	private function getQueryMock()
	{
		return m::mock(Builder::class);
	}

	private function getDbMock()
	{
		return m::mock(Connection::class);
	}

	public function testCreate()
	{
		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('save');

		$this->app->instance(Pledge::class,$model);

		$repo = $this->getRepo();

		// First test we get back a pledge model with the end_date null
		$pledge = $repo->create([
			'user_id' => 1,
			'amount' => 1,
			'type' => 1,
			'streamer_id' => 1,
		]);

		$this->assertTrue(is_a($pledge, Pledge::class));
		$this->assertNull($pledge->end_date);

		// Then test we get back a pledge model with a valid end_date

		$pledge = $repo->create([
			'user_id' => 1,
			'amount' => 1,
			'type' => 1,
			'streamer_id' => 1,
			'end_date' => '01-01-2015'
		]);

		$this->assertTrue(is_a($pledge, Pledge::class));
		$this->assertNotNull($pledge->end_date);

		// Then test that the pledge is not created with an invalid end_date

		$this->setExpectedException('InvalidArgumentException');

		$pledge = $repo->create([
			'user_id' => 1,
			'amount' => 1,
			'type' => 1,
			'streamer_id' => 1,
			'end_date' => 'foo'
		]);

		$this->assertNull($pledge);
	}

	public function testWithStreamer()
	{
		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('with')->with('streamer');

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->withStreamer();

		$this->assertSame($repo,$return);
	}

	public function testWithOwner()
	{
		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('with')->with('owner');

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->withOwner();

		$this->assertSame($repo,$return);
	}

	public function testForStreamer()
	{
		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('whereStreamerId')->with(1);

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->forStreamer(1);

		$this->assertSame($repo,$return);
	}

	public function testFromUser()
	{
		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('whereUserId')->with(1);

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->fromUser(1);

		$this->assertSame($repo,$return);
	}

	public function testIsRunning()
	{
		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('whereRunning')->with(1);

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->isRunning();

		$this->assertSame($repo,$return);
	}

	public function testOrderingByAmount()
	{
		// no value

		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('orderBy')->with('amount','desc');

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->orderingByAmount();

		$this->assertSame($repo,$return);

		// true

		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('orderBy')->with('amount','desc');

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->orderingByAmount(true);

		$this->assertSame($repo,$return);

		// false

		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();
		
		$query->shouldReceive('orderBy')->with('amount','asc');

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->orderingByAmount(false);

		$this->assertSame($repo,$return);
	}

	public function testMostSpent()
	{
		// for later
		$_db = $this->app->make('db');

		$model = $this->getModelMock();
		
		$query = $this->getQueryMock();

		$db = $this->getDbMock();

		$raw = 'sum(`amount` * `times_donated`) as spent';
		$db->shouldReceive('raw')->with($raw)->andReturn($raw);

		$this->app->instance('db',$db);
		
		$query->shouldReceive('select')->with('*',$raw)->andReturn($query);
		$query->shouldReceive('groupBy')->with('user_id');

		$model->shouldReceive('newQuery')->andReturn($query);

		$this->app->instance(Pledge::class,$model);
		
		$repo = $this->getRepo();

		$return = $repo->mostSpent();

		$this->assertSame($repo,$return);

		// reset needed
		$this->app->instance('db',$_db);
	}

}
