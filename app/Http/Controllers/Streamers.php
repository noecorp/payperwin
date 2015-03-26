<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory as View;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Pledges;

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
	 * @return Response
	 */
	public function index()
	{
		$streamers = $this->users->isStreamer()->hasTwitchId()->hasSummonerId()->all();

		$live = $streamers->filter(function($streamer)
		{
			return ($streamer->live === 1);
		});

		$notLive = $streamers->diff($live);

		return $this->view->make('streamers.index')->with(compact('live','notLive'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param Pledges $pledges
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function show(Pledges $pledges, $id)
	{
		$streamer = $this->users->isStreamer()->find($id);

		$feed = $pledges->withOwner()->forStreamer($id)->latest()->limit(10)->all();

		$average = round($pledges->forStreamer($id)->averageAmount(),2);
		$highestPledge = $pledges->withOwner()->forStreamer($id)->orderingByAmount()->find();
		$topPledger = null;

		$stats = compact('average','highestPledge','topPledger');
		
		return $this->view->make('streamers.show')->with(compact('streamer','feed', 'stats'));
	}

}
