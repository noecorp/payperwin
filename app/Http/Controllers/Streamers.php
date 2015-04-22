<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory as View;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Matches;
use App\Contracts\Service\Gurus\Pledge as PledgeGuru;

class Streamers extends Controller {

	/**
	 * The Users Repository implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * The View Factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Create a new users controller instance.
	 *
	 * @param  Users  $users
	 * @param  View  $view
	 *
	 * @return void
	 */
	public function __construct(Users $users, View $view)
	{
		$this->users = $users;
		$this->view = $view;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param Pledges $pledges
	 *
	 * @return Response
	 */
	public function index(Pledges $pledges)
	{
		$streamers = $this->users->isStreamer()->hasTwitchId()->hasSummonerId()->all();

		// Temporary until more efficient solution
		foreach ($streamers as $streamer)
		{
			$streamer->averagePledge = round($pledges->forStreamer($streamer->id)->average('amount'),2);

			$streamer->activePledges = $pledges->forStreamer($streamer->id)->isRunning()->count();
		}

		$live = $streamers->filter(function($streamer)
		{
			return ($streamer->live == 1);
		});

		$notLive = $streamers->diff($live);

		return $this->view->make('streamers.index')->with(compact('live','notLive'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param Pledges $pledges
	 * @param Matches $matches
	 * @param PledgeGuru $guru
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function show(Pledges $pledges, Matches $matches, PledgeGuru $guru, $id)
	{
		$streamer = $this->users->isStreamer()->hasTwitchId()->hasSummonerId()->find($id);

		if (!$streamer) return abort(404);

		$feed = $pledges->withOwner()->forStreamer($id)->latest()->limit(10)->all();

		$average = ($feed->count()) ? round($pledges->forStreamer($id)->average('amount'),2) : null;
		
		$highestPledge = ($feed->count()) ? $pledges->withOwner()->forStreamer($id)->orderingByAmount()->find() : null;
		
		$topPledger = ($feed->count()) ? $pledges->withOwner()->forStreamer($id)->mostSpent()->find() : null;

		$matches =  $matches->latest()->limit(20)->forStreamer($id)->all();

		$wins = $matches->reduce(function($carry, $match)
		{
			return ($match->win) ? ++$carry : $carry;
		},0);

		$losses = $matches->count() - $wins;

		$winLoss = 100 * round($wins / $matches->count(), 2);
		
		$killsAssists = $matches->reduce(function($carry, $match)
		{
			return $carry + $match->kills + $match->assists;
		},0);

		$deaths = $matches->reduce(function($carry, $match)
		{
			return $carry + $match->deaths;
		},0);

		$kda = ($deaths) ? round($killsAssists / $deaths,2) : $killsAssists;

		$stats = compact('average','highestPledge','topPledger','kda','winLoss');
		
		return $this->view->make('streamers.show')->with(compact('streamer','feed', 'stats', 'guru'));
	}

}
