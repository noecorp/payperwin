<?php namespace AppTests\Functional\Commands;

use Mockery as m;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\ParseException;
use App\Commands\CheckTwitchStream as Command;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\Queue;

/**
 * @coversDefaultClass \App\Commands\CheckTwitchStream
 */
class CheckTwitchStreamTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	/**
	 * @small
	 *
	 * @group commands
	 *
	 * @covers ::__construct
	 * @covers ::handle
	 * @covers ::work
	 */
	public function test_handle_with_no_streamer()
	{
		$this->runCommand(999);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
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
	public function test_handle_with_bad_response()
	{
		$this->mockGuzzle(200, ['Content-type' => 'application/json'], 'foo');

		$user = $this->fixture('users',[
			'streamer' => 1,
			'email' => 'foo',
			'username' => 'bar',
			'live' => 0
		]);

		$this->setExpectedException(ParseException::class);

		$this->runCommand($user->id);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
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
	public function test_handle_with_live()
	{
		$this->mockGuzzle(200, ['Content-type' => 'application/json'], '{"stream":"foo"}');

		$user = $this->fixture('users',[
			'streamer' => 1,
			'email' => 'foo',
			'username' => 'bar',
			'live' => 0
		]);

		$this->runCommand($user->id);

		$user = DB::table('users')->find($user->id);

		$this->assertEquals(1,$user->live);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
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
	public function test_handle_with_offline()
	{
		$this->mockGuzzle(200, ['Content-type' => 'application/json'], '{"stream":null}');

		$user = $this->fixture('users',[
			'streamer' => 1,
			'email' => 'foo',
			'username' => 'bar',
			'live' => 1
		]);

		$this->runCommand($user->id);

		$user = DB::table('users')->find($user->id);

		$this->assertEquals(0,$user->live);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));
	}

	private function runCommand($streamerId)
	{
		$command = new Command($streamerId);

		$dispatcher = $this->app->make(QueueingDispatcher::class);
		$dispatcher->dispatchToQueue($command);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$queue = $this->app->make(Queue::class);
		$queue->pop()->fire();
	}

}
