<?php namespace AppTests\Services;

use Mockery as m;
use App\Services\Distribution as DistributionService;
use Illuminate\Support\Collection;

class Distribution extends \AppTests\TestCase {

	public function testFoo()
	{
		$users = $this->getUsersMock();
		$matches = $this->getMatchesMock();
		$pledges = $this->getPledgesMock();

		$users->shouldReceive('find')->with(1);

		$matches->shouldReceive('forStreamer')->with(1)->andReturn($matches);
		$matches->shouldReceive('isUnsettled')->andReturn($matches);
		$matches->shouldReceive('all');

		$pledgesCollection = new Collection([

		]);

		$pledges->shouldReceive('withUser')->andReturn($pledges);
		$pledges->shouldReceive('isRunning')->andReturn($pledges);
		$pledges->shouldReceive('forStreamer')->andReturn($pledges);
		$pledges->shouldReceive('all')->andReturn($pledgesCollection);

		$distribute = new DistributionService($users,$matches,$pledges);

		$distribute->pledgesFor(1);
	}

	public function getUsersMock()
	{
		return m::mock('App\Contracts\Repository\Users');
	}

	public function getPledgesMock()
	{
		return m::mock('App\Contracts\Repository\Pledges');
	}

	public function getMatchesMock()
	{
		return m::mock('App\Contracts\Repository\Matches');
	}

}
