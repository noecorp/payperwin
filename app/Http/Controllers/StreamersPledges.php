<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;

class StreamersPledges extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @param Users $users
	 * @param Pledges $pledges
	 * @param View $view
	 * @param int $streamerId
	 *
	 * @return Response
	 */
	public function index(Users $users, Pledges $pledges, View $view, $streamerId)
	{
		$streamer = $users->havingStreamerId($streamerId);

		$pledges = $pledges->latestForStreamerWithUsers($streamerId, 10);

		return $view->make('streamers.pledges.index')->with(compact('streamer','pledges'));
	}

}
