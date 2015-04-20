<?php namespace AppTests\Unit\Repositories;

use Mockery as m;
use App\Repositories\AbstractRepository;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Events\Dispatcher as Events;
use App\Models\Model;
use App\Exceptions\Repositories\ModelClassMismatch;
use App\Exceptions\Repositories\ModelDoesntExist;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * @coversDefaultClass \App\Repositories\AbstractRepository
 */
class AbstractTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = false;

	private function getCacheMock()
	{
		return m::mock(Cache::class);
	}

	private function getDispatcherMock()
	{
		return m::mock(Events::class);
	}

	private function getModelMock()
	{
		return m::mock(FooModel::class);
	}

	private function getQueryMock()
	{
		return m::mock(Builder::class);
	}

	private function getRepo()
	{
		return new AbstractRepositorySubclass($this->app);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::model
     * @covers ::create
     * @covers ::eventForModelCreated
     */
	public function testCreate()
	{
		$this->create(false);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::model
     * @covers ::create
     * @covers ::quietly
     * @covers ::eventForModelCreated
     */
	public function test_create_quietly()
	{
		$this->create(true);
	}

	private function create($quietly)
	{
		$data = ['foo'=>'bar'];

		$model = new FooModel;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		if (!$quietly)
		{
			$events->shouldReceive('fire')->once()->with(FooCreated::class);
		}

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$result = (!$quietly) ? $repo->create($data) : $repo->quietly()->create($data);

		$this->assertEquals(get_class($result),FooModel::class);
		$this->assertEquals($result->foo,$data['foo']);
		$this->assertTrue($result->exists);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::createAll
     */
	public function testCreateAllDoesNothingWhenEmpty()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		// The events and cache mock would throw errors
		$return = $repo->createAll([]);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::createAll
     * @covers ::query
     * @covers ::eventForModelsCreated
     */
	public function testCreateAll()
	{
		$this->createAll(false);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::createAll
     * @covers ::query
     * @covers ::quietly
     * @covers ::eventForModelsCreated
     */
	public function test_create_all_quietly()
	{
		$this->createAll(true);
	}

	private function createAll($quietly)
	{
		$data = [
			[
				'foo' => 'bar', 'bar' => 'foo'
			],
			[	'foo' => 'baz', 'baz' => 'foo'
			]
		];

		$query = $this->getQueryMock();
		$query->shouldReceive('insert')->once()->with([
			['foo'=>'bar','created_at'=>$this->carbon,'updated_at'=>$this->carbon],
			['foo'=>'baz','created_at'=>$this->carbon,'updated_at'=>$this->carbon]
		]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		if (!$quietly)
		{
			$events->shouldReceive('fire')->once()->with(FoosCreated::class);
		}

		$this->app->instance(FooModel::class,$model);
		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$return = (!$quietly) ? $repo->createAll($data) : $repo->quietly()->createAll($data);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::update
     */
	public function testUpdateThrowsMismatch()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model = new \App\Models\User;

		$this->setExpectedException(ModelClassMismatch::class);

		$repo->update($model, ['foo'=>'bar']);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::update
     */
	public function testUpdateThrowsDoesntExist()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model = new FooModel;

		$this->setExpectedException(ModelDoesntExist::class);

		$repo->update($model, ['foo'=>'bar']);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::update
     */
	public function testUpdateDoesntSaveWhenClean()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model = new FooModel;
		$model->foo = 'bar';
		$model->syncOriginal();
		$model->exists = true;

		// The events and cache mock would throw errors
		$return = $repo->update($model, ['foo'=>'bar']);

		$this->assertSame($model,$return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::update
     * @covers ::eventForModelUpdated
     */
	public function testUpdate()
	{
		$this->update(false);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::update
     * @covers ::quietly
     * @covers ::eventForModelUpdated
     */
	public function test_update_quietly()
	{
		$this->update(true);
	}

	private function update($quietly)
	{
		$model = new FooModel;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$date = Carbon::now();

		if (!$quietly)
		{
			$events->shouldReceive('fire')->once();
		}

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model->foo = 'bar';
		$model->updated_at = $date;
		$model->syncOriginal();
		$model->exists = true;

		$return = (!$quietly) ? $repo->update($model, ['foo'=>'baz']) : $repo->quietly()->update($model, ['foo'=>'baz']);

		$this->assertSame($model,$return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::updateAll
     */
	public function testUpdateAllDoesNothingWhenEmpty()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		// The events and cache mock would throw errors
		$return = $repo->updateAll([],['foo'=>'bar']);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());

		$return = $repo->updateAll([1],[]);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::updateAll
     * @covers ::query
     * @covers ::eventForModelsUpdated
     */
	public function testUpdateAll()
	{
		$this->updateAll(false);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::updateAll
     * @covers ::query
     * @covers ::quietly
     * @covers ::eventForModelsUpdated
     */
	public function test_update_all_quietly()
	{
		$this->updateAll(true);
	}

	private function updateAll($quietly)
	{
		$ids = [1,2,3];
		$data = ['foo'=>'bar','updated_at'=>Carbon::now()];

		$query = $this->getQueryMock();
		$query->shouldReceive('whereIn')->once()->with('id',$ids)->andReturn($query);
		$query->shouldReceive('update')->once()->with(m::any());

		unset($data['updated_at']);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		if (!$quietly)
		{
			$events->shouldReceive('fire')->once()->with(FoosUpdated::class);
		}

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = (!$quietly) ? $repo->updateAll($ids, $data) : $repo->quietly()->updateAll($ids, $data);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::increment
     * @covers ::update
     */
	public function testIncrement()
	{
		$this->increment(false);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::increment
     * @covers ::quietly
     * @covers ::update
     */
	public function test_increment_quietly()
	{
		$this->increment(true);
	}

	private function increment($quietly)
	{
		$column = 'foo';

		$model = new FooModel;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$date = Carbon::now();

		if (!$quietly)
		{
			$events->shouldReceive('fire')->once();
		}

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model->$column = 1;
		$model->updated_at = $date;
		$model->syncOriginal();
		$model->exists = true;

		$return = (!$quietly) ? $repo->increment($model, $column, 3) : $repo->quietly()->increment($model, $column, 3);

		$this->assertSame($model,$return);
		$this->assertEquals($model->$column,4);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::increment
     * @covers ::update
     */
	public function testIncrementThrowsMismatch()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model = new \App\Models\User;

		$this->setExpectedException(ModelClassMismatch::class);

		$repo->increment($model, 'foo', 1);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::increment
     * @covers ::update
     */
	public function testIncrementThrowsDoesntExist()
	{
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model = new FooModel;

		$this->setExpectedException(ModelDoesntExist::class);

		$repo->increment($model, 'foo', 1);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::incrementAll
     * @covers ::query
     * @covers ::eventForModelsUpdated
     */
	public function test_increment_all()
	{
		$this->incrementAll(false);
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::incrementAll
     * @covers ::query
     * @covers ::quietly
     * @covers ::eventForModelsUpdated
     */
	public function test_increment_all_quietly()
	{
		$this->incrementAll(true);
	}

	private function incrementAll($quietly)
	{
		$column = 'foo';
		$ids = [1,2];

		$query = $this->getQueryMock();
		$query->shouldReceive('whereIn')->once()->with('id',$ids)->andReturn($query);
		$query->shouldReceive('increment')->once()->with($column, 3, m::any());

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		if (!$quietly)
		{
			$events->shouldReceive('fire')->once()->with(FoosUpdated::class);
		}

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		if (!$quietly)
		{
			$repo->incrementAll($ids, $column, 3);
		}
		else
		{
			$repo->quietly()->incrementAll($ids, $column, 3);
		}

		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::incrementAll
     */
	public function test_increment_all_does_nothing_with_no_ids()
	{
		$model = $this->getModelMock();
		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$this->assertNull($repo->incrementAll([], 'foo', 1));
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::find
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function testFindWithoutId()
	{
		$id = 777;

		$query = $this->getQueryMock();
		$query->shouldReceive('limit')->once()->with(1);
		$query->shouldReceive('getQuery')->once()->andReturn($query);
		$query->shouldReceive('toSql')->once()->andReturn('query');
		$query->shouldReceive('getBindings')->once()->andReturn(['bar']);
		$query->shouldReceive('getEagerLoads')->times(3)->andReturn([]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$returnModel = new FooModel;
		$returnModel->id = $id;

		$cache->shouldReceive('tags->rememberForever')->andReturn($returnModel);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->find();

		$this->assertSame($return, $returnModel);
		$this->assertEquals($id, $return->id);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::find
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function testFindWithId()
	{
		$id = 777;

		$returnModel = new FooModel;
		$returnModel->id = $id;

		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('whereId')->once()->with($id);
		$query->shouldReceive('getQuery')->once()->andReturn($query);
		$query->shouldReceive('toSql')->once()->andReturn('query');
		$query->shouldReceive('getBindings')->once()->andReturn(['bar']);
		$query->shouldReceive('getEagerLoads')->times(3)->andReturn([]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags->rememberForever')->andReturn($returnModel);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->find($id);

		$this->assertSame($return, $returnModel);
		$this->assertEquals($id, $return->id);
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::find
     * @covers ::query
     * @covers ::freshly
     * @covers ::reset
     */
	public function test_find_freshly()
	{
		$id = 777;

		$returnModel = new FooModel;
		$returnModel->id = $id;

		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('limit')->once()->with(1);
		$query->shouldReceive('first')->once()->andReturn($returnModel);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->freshly()->find();

		$this->assertSame($return, $returnModel);
		$this->assertEquals($id, $return->id);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::find
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function test_find_with_id_from_stored_object()
	{
		$id = 777;

		$returnModel = new FooModel;
		$returnModel->id = $id;

		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('whereId')->times(1)->with($id);
		$query->shouldReceive('getQuery')->times(1)->andReturn($query);
		$query->shouldReceive('toSql')->times(1)->andReturn('query');
		$query->shouldReceive('getBindings')->times(1)->andReturn(['bar']);
		$query->shouldReceive('getEagerLoads')->times(5)->andReturn([]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->times(3)->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags->rememberForever')->once()->andReturn($returnModel);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$repo->find($id);
		$repo->find($id);
		$return = $repo->find($id);

		$this->assertSame($return, $returnModel);
		$this->assertEquals($id, $return->id);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());	
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::find
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function test_find_with_id_with_relation_without_stored_object()
	{
		$id = 777;

		$returnModel = new FooModel;
		$returnModel->id = $id;

		$returnModel2 = new FooModel;
		$returnModel2->id = $id;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags->rememberForever')->once()->andReturn($returnModel);
		$cache->shouldReceive('tags->rememberForever')->once()->andReturn($returnModel2);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$repo->withBar()->find($id);
		$return = $repo->withBar()->find($id);

		$this->assertNotSame($return, $returnModel);
		$this->assertEquals($id, $return->id);
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::all
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function testAll()
	{
		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('getQuery')->once()->andReturn($query);
		$query->shouldReceive('toSql')->once()->andReturn('query');
		$query->shouldReceive('getBindings')->once()->andReturn(['bar']);
		$query->shouldReceive('getEagerLoads')->times(2)->andReturn([]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->with([$model->getTable()])->andReturn($cache);
		$cache->shouldReceive('rememberForever')->once()->with(md5('query  bar'),m::any())->andReturn('baz');

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->all();

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::find
     * @covers ::query
     * @covers ::freshly
     * @covers ::reset
     */
	public function test_all_freshly()
	{
		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('get')->once()->andReturn('baz');

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->freshly()->all();

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::count
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function testCount()
	{
		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('getQuery')->once()->andReturn($query);
		$query->shouldReceive('toSql')->once()->andReturn('query');
		$query->shouldReceive('getBindings')->once()->andReturn(['bar']);
		$query->shouldReceive('getEagerLoads')->times(2)->andReturn([]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->with([$model->getTable()])->andReturn($cache);
		$cache->shouldReceive('rememberForever')->once()->with(md5('query count bar'),m::any())->andReturn('baz');

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->count();

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::count
     * @covers ::query
     * @covers ::freshly
     * @covers ::reset
     */
	public function test_count_freshly()
	{
		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('count')->once()->with('id')->andReturn('baz');

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->freshly()->count();

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::average
     * @covers ::calculate
     * @covers ::query
     * @covers ::getQueryHash
     * @covers ::getCacheTags
     * @covers ::reset
     */
	public function testAverage()
	{
		$column = 'foo';

		$query = $this->getQueryMock();
		$query->shouldReceive('getQuery')->once()->andReturn($query);
		$query->shouldReceive('toSql')->once()->andReturn('query');
		$query->shouldReceive('getBindings')->once()->andReturn(['bar']);
		$query->shouldReceive('getEagerLoads')->times(2)->andReturn([]);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->with([$model->getTable()])->andReturn($cache);
		$cache->shouldReceive('rememberForever')->once()->with(md5('query avg,foo bar'),m::any())->andReturn('baz');

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->average($column);

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::average
     * @covers ::calculate
     * @covers ::query
     * @covers ::freshly
     * @covers ::reset
     */
	public function test_average_freshly()
	{
		$column = 'foo';

		$query = $this->getQueryMock();
		$query->shouldReceive('avg')->once()->with($column)->andReturn('baz');

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->freshly()->average($column);

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::latest
     * @covers ::query
     */
	public function testLatest()
	{
		$query = $this->getQueryMock();
		$query->shouldReceive('orderBy')->once()->with('created_at','desc');

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->latest();

		$this->assertSame($return, $repo);
		$this->assertNotNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::earliest
     * @covers ::query
     */
	public function testEarliest()
	{
		$query = $this->getQueryMock();
		$query->shouldReceive('orderBy')->once()->with('created_at','asc');

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->earliest();

		$this->assertSame($return, $repo);
		$this->assertNotNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::after
     * @covers ::query
     */
	public function testAfter()
	{
		$date = Carbon::now();

		$query = $this->getQueryMock();
		$query->shouldReceive('where')->once()->with('created_at','>',$date);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->after($date);

		$this->assertSame($return, $repo);
		$this->assertNotNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::before
     * @covers ::query
     */
	public function testBefore()
	{
		$date = Carbon::now();

		$query = $this->getQueryMock();
		$query->shouldReceive('where')->once()->with('created_at','<',$date);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->before($date);

		$this->assertSame($return, $repo);
		$this->assertNotNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::limit
     * @covers ::query
     */
	public function testLimitNoPage()
	{
		$take = 5;

		$query = $this->getQueryMock();
		$query->shouldReceive('limit')->once()->with($take);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->limit($take);

		$this->assertSame($return, $repo);
		$this->assertNotNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

	/**
     * @small
     *
     * @group repositories
     *
     * @covers ::__construct
     * @covers ::limit
     * @covers ::query
     */
	public function testLimitWithPage()
	{
		$take = 5;
		$page = 2;

		$query = $this->getQueryMock();
		$query->shouldReceive('limit')->once()->with($take);
		$query->shouldReceive('skip')->once()->with($take * ($page-1));

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->limit($take,$page);

		$this->assertSame($return, $repo);
		$this->assertNotNull($repo->getQuery());
		$this->assertTrue($repo->getUseCache());
		$this->assertTrue($repo->getSendEvents());
	}

}

class FooModel extends Model {
	protected $table = 'foos';

	protected $fillable = ['foo'];

	public function save(array $options = array())
	{
		$this->exists = true;
	}

	public function bar()
	{
		return $this->hasMany('\AppTests\Unit\Repositories\Foo2Model');
	}
}

class Foo2Model extends Model {
	protected $table = 'foo2s';

	protected $fillable = ['foo'];

	public function save(array $options = array())
	{
		$this->exists = true;
	}
}

class AbstractRepositorySubclass extends AbstractRepository {

	public function getQuery()
	{
		return $this->query;
	}

	public function withBar()
	{
		$this->query()->with('bar');

		return $this;
	}

	public function getUseCache()
	{
		return $this->useCache;
	}

	public function getSendEvents()
	{
		return $this->sendEvents;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function model()
	{
		return FooModel::class;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function eventForModelCreated(Model $model)
	{
		return FooCreated::class;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function eventForModelsCreated()
	{
		return FoosCreated::class;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function eventForModelUpdated(Model $model, array $changed)
	{
		return FooUpdated::class;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function eventForModelsUpdated()
	{
		return FoosUpdated::class;
	}
}

class FooCreated extends \App\Events\Repositories\ModelEvent {}
class FoosCreated extends \App\Events\Repositories\ModelsEvent {}
class FooUpdated extends \App\Events\Repositories\ModelEvent {}
class FoosUpdated extends \App\Events\Repositories\ModelsEvent {}
