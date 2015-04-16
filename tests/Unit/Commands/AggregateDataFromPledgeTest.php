<?php namespace AppTests\Unit\Commands;

use Mockery as m;
use App\Commands\AggregateDataFromPledge as Command;
use App\Models\User;
use App\Models\Pledge;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Aggregations;
use App\Contracts\Service\Acidifier;
use App\Contracts\Service\Gurus\Aggregation as Guru;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * @coversDefaultClass \App\Commands\AggregateDataFromPledge
 */
class AggregateDataFromPledgeTest extends \AppTests\TestCase {

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
	 */
	public function test_handle_with_no_pledge()
	{

		$pledges = $this->getPledgesMock();
		$pledges->shouldReceive('find')->once()->andReturn(null);

		$aggregations = $this->getAggregationsMock();
		$guru = $this->getGuruMock();
		$acid = $this->getAcidifierMock();

		$command = new Command(999);

		$this->app->instance(Pledges::class,$pledges);
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
	 * @covers ::updateOrCreate
	 */
	public function test_handle_with_no_aggregations()
	{
		$pledge = $this->getPledgeMock();
		$pledge->shouldReceive('getAttribute')->with('user_id');
		$pledge->shouldReceive('getAttribute')->with('streamer_id');
		$pledge->shouldReceive('getAttribute')->with('created_at')->andReturn(new Carbon('2015-01-02 03:04:05'));

		$pledges = $this->getPledgesMock();
		$pledges->shouldReceive('find')->once()->andReturn($pledge);

		$aggregations = $this->getAggregationsMock();

		$userCollection = new Collection([
			
		]);
		$streamerCollection = new Collection([
			
		]);

		$aggregations->shouldReceive('forUser->forReason->all')->andReturn($userCollection);
		$aggregations->shouldReceive('forUser->forReason->all')->andReturn($streamerCollection);
		$aggregations->shouldReceive('create')->times(10);

		$guru = $this->getGuruMock();
		$guru->shouldReceive('pledgeFromUser')->andReturn(1);
		$guru->shouldReceive('pledgeToStreamer')->andReturn(2);
		$daily = 1; $weekly = 2; $monthly = 3; $yearly = 4; $total = 5;
		$guru->shouldReceive('types')->andReturn([$daily,$weekly,$monthly,$yearly,$total]);
		$guru->shouldReceive('daily')->andReturn($daily);
		$guru->shouldReceive('weekly')->andReturn($weekly);
		$guru->shouldReceive('monthly')->andReturn($monthly);
		$guru->shouldReceive('yearly')->andReturn($yearly);
		$guru->shouldReceive('total')->andReturn($total);

		$acid = $this->getAcidifierMock();
		$acid->shouldReceive('transaction')->once()->with(m::on(function(\Closure $closure) use ($userCollection, $streamerCollection) {
			$closure($userCollection, $streamerCollection);
			return true;
		}));

		$command = new Command(999);

		$this->app->instance(Pledges::class,$pledges);
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

	public function getPledgeMock()
	{
		return m::mock(Pledge::class);
	}

	public function getPledgesMock()
	{
		return m::mock(Pledges::class);
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
