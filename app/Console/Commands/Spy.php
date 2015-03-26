<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use App\Commands\CheckTwitchStream;

class Spy extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'spy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check streamers for live status';

	/**
	 * Execute the console command.
	 *
	 * @param Users $users
	 * @param QueueingDispatcher $dispatcher
	 *
	 * @return mixed
	 */
	public function handle(Users $users, QueueingDispatcher $dispatcher)
	{
		$streamers = $users->isStreamer()->hasTwitchId()->all();

		foreach ($streamers as $streamer)
		{
			$dispatcher->dispatchToQueue($this->checkTwitchStreamCommand($streamer->id));
		}
	}

	protected function checkTwitchStreamCommand($streamerId)
	{
		return new CheckTwitchStream($streamerId);
	}

}
