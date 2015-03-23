<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Matches;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use App\Commands\FetchMatches;

class Impress extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'impress';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch new match data for all streamers';

	/**
	 * Execute the console command.
	 *
	 * @param Users $users
	 * @param Matches $matches
	 * @param QueueingDispatcher $dispatcher
	 *
	 * @return mixed
	 */
	public function handle(Users $users, Matches $matches, QueueingDispatcher $dispatcher)
	{
		$streamers = $users->isStreamer()->hasTwitchId()->hasSummonerId()->all();

		foreach ($streamers as $streamer)
		{
			$latest = $matches->orderingByMatchDate()->forStreamer($streamer->id)->find();

			if (!$latest || Carbon::now()->subHour()->gte($latest->match_date))
			{
				if ($latest) $latest = $latest->id;

				$dispatcher->dispatchToQueue($this->fetchMatchesCommand($streamer->id,$latest));
			}
		}
	}

	protected function fetchMatchesCommand($streamerId,$matchId)
	{
		return new FetchMatches($streamerId,$matchId);
	}

}
