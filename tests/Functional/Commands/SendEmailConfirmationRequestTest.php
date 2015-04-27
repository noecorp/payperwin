<?php namespace AppTests\Functional\Commands;

use Mockery as m;
use Illuminate\Support\Facades\DB;
use App\Commands\SendEmailConfirmationRequest as Command;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\Queue;

/**
 * @coversDefaultClass \App\Commands\SendEmailConfirmationRequest
 */
class SendEmailConfirmationRequestTest extends \AppTests\TestCase {

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
	 */
	public function test_handle_exception()
	{
		$this->runCommand(999);

		$this->assertEquals(1, DB::table('jobs')->count());

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$job = json_decode($jobs[0]->payload);
		$command = unserialize($job->data->command);

		$this->assertEquals(Command::class, get_class($command));
	}

	/**
	 * @small
	 *
	 * @group commands
	 *
	 * @covers ::__construct
	 * @covers ::handle
	 */
	public function test_handle()
	{
		$random = str_random(16);

		$user = $this->fixture('users', [
			'email' => $random.'@email.com',
			'username' => 'bar',
			'confirmation_code' => 'foobar'
		]);

		$this->runCommand($user->id);

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(0,count($jobs));

		$logs = $this->getLog();

		$this->assertContains(view('emails.confirm',['username'=>'bar','code'=>'foobar'])->render(), $logs);
	}

	private function runCommand($userId)
	{
		$command = new Command($userId);

		$dispatcher = $this->app->make(QueueingDispatcher::class);
		$dispatcher->dispatchToQueue($command);

		$queue = $this->app->make(Queue::class);
		$queue->pop()->fire();
	}

}
