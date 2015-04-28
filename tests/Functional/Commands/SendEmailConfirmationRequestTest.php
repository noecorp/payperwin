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
	 * @covers ::work
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

		$jobs = DB::table('jobs')->get();
		
		$this->assertEquals(1,count($jobs));

		$queue = $this->app->make(Queue::class);
		$queue->pop()->fire();
	}

}
