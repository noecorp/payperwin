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

class AbstractTest extends \AppTests\TestCase {

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

	public function testCreate()
	{
		$data = ['foo'=>'bar'];

		$model = new FooModel;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$events->shouldReceive('fire')->once()->with(FooCreated::class);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$result = $repo->create($data);

		$this->assertEquals(get_class($result),FooModel::class);
		$this->assertEquals($result->foo,$data['foo']);
		$this->assertTrue($result->exists);
		$this->assertNull($repo->getQuery());
	}

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
	}

	public function testCreateAll()
	{
		$data = [
			[
				'foo' => 'bar',
			],
			[	'foo' => 'baz',
			]
		];

		$query = $this->getQueryMock();
		$query->shouldReceive('insert')->once()->with(m::any());

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$events->shouldReceive('fire')->once()->with(FoosCreated::class);

		$this->app->instance(FooModel::class,$model);
		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$return = $repo->createAll($data);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
	}

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
	}

	public function testUpdate()
	{
		$model = new FooModel;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$events->shouldReceive('fire')->once()->with(FooUpdated::class);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model->foo = 'bar';
		$model->syncOriginal();
		$model->exists = true;

		$return = $repo->update($model, ['foo'=>'baz']);

		$this->assertSame($model,$return);
		$this->assertNull($repo->getQuery());
	}

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

		$return = $repo->updateAll([1],[]);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
	}

	public function testUpdateAll()
	{
		$ids = [1,2,3];
		$data = ['foo'=>'bar','updated_at'=>Carbon::now()];

		$query = $this->getQueryMock();
		$query->shouldReceive('whereIn')->once()->with('id',$ids)->andReturn($query);
		$query->shouldReceive('update')->once()->with($data);

		unset($data['updated_at']);

		$model = $this->getModelMock()->makePartial();
		$model->shouldReceive('newQuery')->once()->andReturn($query);

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$events->shouldReceive('fire')->once()->with(FoosUpdated::class);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->updateAll($ids, $data);

		$this->assertNull($return);
		$this->assertNull($repo->getQuery());
	}

	public function testIncrement()
	{
		$column = 'foo';

		$model = new FooModel;

		$cache = $this->getCacheMock();
		$events = $this->getDispatcherMock();

		$cache->shouldReceive('tags')->once()->with($model->getTable())->andReturn($cache);
		$cache->shouldReceive('flush')->once();

		$events->shouldReceive('fire')->once()->with(FooUpdated::class);

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);

		$repo = $this->getRepo();

		$model->$column = 1;
		$model->syncOriginal();
		$model->exists = true;

		$return = $repo->increment($model, $column, 3);

		$this->assertSame($model,$return);
		$this->assertEquals($model->$column,4);
		$this->assertNull($repo->getQuery());
	}

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

	public function testFindWithoutId()
	{
		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('limit')->once()->with(1);
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

		$return = $repo->find();

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
	}

	public function testFindWithId()
	{
		$id = 1;

		$query = $this->getQueryMock()->makePartial();
		$query->shouldReceive('whereId')->once()->with($id);
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

		$return = $repo->find($id);

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
	}

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
	}

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
	}

	public function testAverage()
	{
		$column = 'foo';

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
		$cache->shouldReceive('rememberForever')->once()->with(md5('query avg,foo bar'),m::any())->andReturn('baz');

		$this->app->instance(Events::class,$events);
		$this->app->instance(Cache::class,$cache);
		$this->app->instance(FooModel::class,$model);

		$repo = $this->getRepo();

		$return = $repo->average($column);

		$this->assertEquals($return, 'baz');
		$this->assertNull($repo->getQuery());
	}

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
	}

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
	}

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
	}

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
	}

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
	}

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
	}

}

class FooModel extends Model {
	protected $table = 'foos';

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
	protected function eventForModelUpdated(Model $model)
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