<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory as View;
use Illuminate\Contracts\Auth\Guard;
use App\Contracts\Repository\Aggregations;
use App\Contracts\Repository\Pledges;
use App\Contracts\Service\Gurus\Aggregation as Guru;
use Carbon\Carbon;
use App\Models\Pledge;
use Illuminate\Http\Request;

class Dashboard extends Controller {

	/**
	 * View factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Authentication implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Aggregations Repository implementation.
	 *
	 * @var Aggregations
	 */
	protected $aggregations;

	/**
	 * Pledges Repository implementation.
	 *
	 * @var Pledges
	 */
	protected $pledges;

	/**
	 * Aggregation Guru implementation.
	 *
	 * @var Guru
	 */
	protected $guru;

	/**
	 * Request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new controller instance.
	 *
	 * @param View $view
	 * @param Guard $auth
	 * @param Aggregations $aggregations
	 * @param Pledges $pledges
	 * @param Guru $guru
	 * @param Request $request
	 *
	 * @return void
	 */
	public function __construct(View $view, Guard $auth, Aggregations $aggregations, Pledges $pledges, Guru $guru, Request $request)
	{
		$this->view = $view;
		$this->auth = $auth;
		$this->aggregations = $aggregations;
		$this->pledges = $pledges;
		$this->guru = $guru;
		$this->request = $request;
		
		$this->middleware('auth');
		$this->middleware('start');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		$created = $this->pledgesCreated();

		$pledges['active'] = $created->filter(function(Pledge $pledge)
		{
			return ($pledge->running === true);
		});

		$pledges['inactive'] = $created->filter(function(Pledge $pledge)
		{
			return ($pledge->running === false);
		});

		$spending['history'] = $this->dailyAmounts($this->guru->paidByUser(), (!$created->isEmpty()));

		$pledges['stats'] = $this->pledgeStats();

		$earnings['summary'] = $this->earningsSummary(($pledges['stats']['total'] > 0));

		$earnings['history'] = $this->dailyAmounts($this->guru->paidToStreamer(), ($pledges['stats']['total'] > 0));

		$pledges['latest'] = ($pledges['stats']['total']) ? $this->latestPledges() : new \Illuminate\Database\Eloquent\Collection;

		$limit = 10;

		$page = (int) $this->request->get('page');
		if ($page < 1) $page = 1;

		$type = ($this->request->get('leaderboards') == 'biggest') ? 'biggest' : 'total';

		if ($pledges['stats']['total'])
		{
			if ($type == 'biggest')
			{
				$leaderboard['leaders'] = $this->leaderboard('biggest', $limit, $page);
			}
			else
			{
				$leaderboard['leaders'] = $this->leaderboard('total', $limit, $page);
			}
		}
		else
		{
			$leaderboard['leaders'] = [];
		}

		$leaderboard['type'] = $type;

		$count = ($pledges['stats']['total']) ? $this->countSpenders() : 0;

		$leaderboard['more'] = ($count > $page * $limit);

		$leaderboard['less'] = ($page > 1);

		return $this->view->make('dashboard.index', compact('earnings','pledges', 'leaderboard', 'spending'));
	}

	/**
	 * Get all pledges created by the current user.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	protected function pledgesCreated()
	{
		return $this->pledges->fromUser($this->auth->user()->id)->withStreamer()->all();
	}

	/**
	 * Get the summarised earnings statistics.
	 *
	 * @param boolean $full Whether or not to return non-empty summary.
	 *
	 * @return array
	 */
	protected function earningsSummary($full = true)
	{
		$summary = [
			'today' => 0,
			'week' => 0,
			'month' => 0,
			'total' => 0
		];

		if ($full)
		{
			$aggregations = $this->aggregations->forUser($this->auth->user()->id)
				->forDate(Carbon::now())
				->forReason($this->guru->paidToStreamer())
				->all();

			foreach ($aggregations as $aggregation)
			{
				if ($aggregation->type == $this->guru->daily())
				{
					$summary['today'] = $aggregation->amount;
				}
				else if ($aggregation->type == $this->guru->weekly())
				{
					$summary['week'] = $aggregation->amount;
				}
				else if ($aggregation->type == $this->guru->monthly())
				{
					$summary['month'] = $aggregation->amount;
				}
				else if ($aggregation->type == $this->guru->total())
				{
					$summary['total'] = $aggregation->amount;
				} else
				{
					continue;
				}
			}
		}

		return $summary;
	}

	/**
	 * Get the graphable daily earnings list.
	 *
	 * @param int $reason Paid to streamer / paid by user.
	 * @param boolean $full Whether or not to return non-empty summary.
	 *
	 * @return array
	 */
	protected function dailyAmounts($reason, $full = true)
	{
		$history = [
			'amounts' => [],
			'days' => [],
		];

		if ($full)
		{
			$now = Carbon::now();

			$limit = Carbon::now()->subDays(14);

			$dailies = $this->aggregations->forUser($this->auth->user()->id)
				->isDaily()
				->forReason($reason)
				->since($limit)
				->all();

			$limit->subDays(1);

			for ($i = 0; $i < 15; $i++)
			{
				$amount = 0;

				$limit->addDay();
				$full = $limit->format('y-m-d');
				
				$day = '';

				if (($now->dayOfYear - $limit->dayOfYear) % 7 == 0)
				{
					$day = ($full == $now->format('y-m-d')) ? 'Today' : $limit->format('d/m');
				}

				$history['days'][] = $day;

				foreach ($dailies as $aggregation)
				{
					$date = Carbon::createFromFormat('y-m-d',$aggregation->year.'-'.$aggregation->month.'-'.$aggregation->day);

					if ($date->format('y-m-d') == $full)
					{
						$amount = $aggregation->amount;
					}
				}

				$history['amounts'][] = $amount;
			}
		}

		return $history;
	}

	/**
	 * Get the summarised pledge statistics.
	 *
	 * @return array
	 */
	protected function pledgeStats()
	{
		$stats['total'] = $this->pledges->forStreamer($this->auth->user()->id)->count();

		$stats['active'] = ($stats['total']) ? $this->pledges->forStreamer($this->auth->user()->id)->isRunning()->count() : 0;

		$stats['average'] = ($stats['total']) ? $this->pledges->forStreamer($this->auth->user()->id)->average('amount') : 0;

		$stats['new-today'] = ($stats['total']) ? $this->pledges->forStreamer($this->auth->user()->id)->today()->count() : 0;

		$stats['new-week'] = ($stats['total']) ? $this->pledges->forStreamer($this->auth->user()->id)->thisWeek()->count() : 0;

		$stats['new-month'] = ($stats['total']) ? $this->pledges->forStreamer($this->auth->user()->id)->thisMonth()->count() : 0;

		return $stats;
	}

	/**
	 * Get the latest pledges list.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	protected function latestPledges()
	{
		return $this->pledges->withOwner()->forStreamer($this->auth->user()->id)->latest()->limit(10)->all();
	}

	/**
	 * Get the leaderboard listings.
	 *
	 * @param string $type Type of leaderboard
	 * @param int $limit Number of results to return
	 * @param int $page Results page
	 *
	 * @return array
	 */
	protected function leaderboard($type, $limit, $page)
	{
		$this->pledges->forStreamer($this->auth->user()->id)->withOwner()->limit($limit, $page);

		$leaderboard = ($type == 'biggest') ? $this->pledges->donated()->orderingByAmount()->all() : $this->pledges->mostSpent()->all();

		$mapped = $leaderboard->map(function(Pledge $pledge) use ($type)
		{
			return ['id' => $pledge->user_id, 'amount' => ($type == 'biggest') ? $pledge->amount : $pledge->spent, 'username' => $pledge->owner->username];
		})->toArray();

		$c = count($mapped);

		for ($i = 0; $i < $c; $i++)
		{
			$mapped[$i]['rank'] = $limit * ($page - 1) + $i + 1;
		}

		return $mapped;
	}

	/**
	 * Get the total number of pledgers.
	 *
	 * @return int
	 */
	protected function countSpenders()
	{
		return $this->pledges->forStreamer($this->auth->user()->id)->countPledgers();
	}

}
