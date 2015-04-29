<?php namespace AppTests\Unit\Commands;

use Mockery as m;
use App\Commands\AggregateDataFromUserUpdate as Command;
use App\Models\User;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Aggregations;
use App\Contracts\Service\Acidifier;
use App\Contracts\Service\Gurus\Aggregation as Guru;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @coversDefaultClass \App\Commands\AggregateDataFromUserUpdate
 */
class AggregateDataFromUserUpdateTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = false;

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::work
	 */
	public function test_handle_with_no_user()
	{
		$users = $this->getUsersMock();
		$users->shouldReceive('find')->once()->with(999)->andReturn(null);

		$aggregations = $this->getAggregationsMock();
		$guru = $this->getGuruMock();
		$acid = $this->getAcidifierMock();

		$command = new Command(999, ['foo'=>'bar'], ['foo'=>'baz']);

		$this->app->instance(Users::class,$users);
		$this->app->instance(Aggregations::class,$aggregations);
		$this->app->instance(Acidifier::class,$acid);
		$this->app->instance(Guru::class,$guru);
		$this->app->instance(Command::class, $command);

		$this->assertNull($this->app->call(Command::class.'@handle'));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::work
	 * @covers ::getUserAggregations
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_no_existing_aggregations_for_funds()
	{
		$changed = [
			'funds' => 10,
			'updated_at' => '2013-01-02 03:04:05'
		];

		$current = [
			'funds' => 0,
			'updated_at' => '2014-11-11 11:11:11'
		];

		$user = $this->getUserMock();
		$user->shouldReceive('getAttribute')->with('funds')->andReturn(0);
		$user->shouldReceive('getAttribute')->with('id')->andReturn(999);
		$user->shouldReceive('getAttribute')->with('updated_at')->andReturn('2014-11-11 11:11:11');

		$users = $this->getUsersMock();
		$users->shouldReceive('find')->once()->andReturn($user);

		$aggregations = $this->getAggregationsMock();

		$userCollection = new Collection([
			
		]);

		$guru = $this->getGuruMock();
		$guru->shouldReceive('paidByUser')->andReturn(1);
		$daily = 1; $weekly = 2; $monthly = 3; $yearly = 4; $total = 5;
		$guru->shouldReceive('types')->andReturn([$daily,$weekly,$monthly,$yearly,$total]);
		$guru->shouldReceive('daily')->andReturn($daily);
		$guru->shouldReceive('weekly')->andReturn($weekly);
		$guru->shouldReceive('monthly')->andReturn($monthly);
		$guru->shouldReceive('yearly')->andReturn($yearly);
		$guru->shouldReceive('total')->andReturn($total);

		$aggregations->shouldReceive('forUser->forReason->forDate->all')->andReturn($userCollection);
		$aggregations->shouldReceive('create')->once()->with(
			[
				'reason' => $guru->paidByUser(),
				'amount' => $changed['funds'] - $current['funds'],
				'type' => $daily,
				'user_id' => 999,
				'day' => 11,
				'week' => 0,
				'month' => 11,
				'year' => 14
			]);
		$aggregations->shouldReceive('create')->once()->with(
			[
				'reason' => $guru->paidByUser(),
				'amount' => $changed['funds'] - $current['funds'],
				'type' => $weekly,
				'user_id' => 999,
				'day' => 0,
				'week' => 46,
				'month' => 0,
				'year' => 14
			]);
		$aggregations->shouldReceive('create')->once()->with(
			[
				'reason' => $guru->paidByUser(),
				'amount' => $changed['funds'] - $current['funds'],
				'type' => $monthly,
				'user_id' => 999,
				'day' => 0,
				'week' => 0,
				'month' => 11,
				'year' => 14
			]);
		$aggregations->shouldReceive('create')->once()->with(
			[
				'reason' => $guru->paidByUser(),
				'amount' => $changed['funds'] - $current['funds'],
				'type' => $yearly,
				'user_id' => 999,
				'day' => 0,
				'week' => 0,
				'month' => 0,
				'year' => 14
			]);
		$aggregations->shouldReceive('create')->once()->with(
			[
				'reason' => $guru->paidByUser(),
				'amount' => $changed['funds'] - $current['funds'],
				'type' => $total,
				'user_id' => 999,
				'day' => 0,
				'week' => 0,
				'month' => 0,
				'year' => 0
			]);

		$acid = $this->getAcidifierMock();
		$acid->shouldReceive('transaction')->once()->with(m::on(function(\Closure $closure) use ($userCollection) {
			$closure($userCollection, null);
			return true;
		}));

		$command = new Command(999, $changed, $current);

		$this->app->instance(Users::class,$users);
		$this->app->instance(Aggregations::class,$aggregations);
		$this->app->instance(Acidifier::class,$acid);
		$this->app->instance(Guru::class,$guru);
		$this->app->instance(Command::class, $command);

		$this->assertNull($this->app->call(Command::class.'@handle'));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
 	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::work
	 */
	public function test_handle_with_out_of_date_fields()
	{
		$changed = [
			'funds' => 10,
			'updated_at' => '2013-01-02 03:04:05'
		];

		$current = [
			'funds' => 0,
			'updated_at' => '2014-11-11 11:11:11'
		];

		$user = $this->getUserMock();
		$user->shouldReceive('getAttribute')->with('funds')->andReturn(0);
		$user->shouldReceive('getAttribute')->with('id')->andReturn(999);
		$user->shouldReceive('getAttribute')->with('updated_at')->andReturn('2014-12-12 12:12:12');

		$users = $this->getUsersMock();
		$users->shouldReceive('find')->once()->andReturn($user);

		$aggregations = $this->getAggregationsMock();
		$guru = $this->getGuruMock();
		$acid = $this->getAcidifierMock();

		$command = new Command(999, $changed, $current);

		$this->app->instance(Users::class,$users);
		$this->app->instance(Aggregations::class,$aggregations);
		$this->app->instance(Acidifier::class,$acid);
		$this->app->instance(Guru::class,$guru);
		$this->app->instance(Command::class, $command);

		$this->assertNull($this->app->call(Command::class.'@handle'));
	}

	public function getGuruMock()
	{
		return m::mock(Guru::class);
	}

	public function getUserMock()
	{
		return m::mock(User::class);
	}

	public function getUsersMock()
	{
		return m::mock(Users::class);
	}

	public function getAcidifierMock()
	{
		return m::mock(Acidifier::class);
	}

	public function getAggregationsMock()
	{
		return m::mock(Aggregations::class);
	}

}
