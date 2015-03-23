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
	 * @param int $id
	 *
	 * @return Response
	 */
	public function index(Users $users, Pledges $pledges, View $view, $id)
	{
		$streamer = $users->isStreamer()->find($id);

		$pledges = $pledges->withOwner()->latest()->limit(10)->forStreamer($id)->all();

		return $view->make('streamers.pledges.index')->with(compact('streamer','pledges'));
	}

}
