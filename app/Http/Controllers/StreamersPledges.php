<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use App\Contracts\Service\Gurus\Pledge as PledgeGuru;
use Illuminate\Http\Request;

class StreamersPledges extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @param Users $users
	 * @param Pledges $pledges
	 * @param View $view
	 * @param Request $request
	 * @param Response $response
	 * @param PledgeGuru $guru
	 * @param int $id
	 *
	 * @return Response
	 */
	public function index(Users $users, Pledges $pledges, View $view, Request $request, Response $response, PledgeGuru $guru, $id)
	{
		$streamer = $users->isStreamer()->find($id);

		if (!$streamer) return abort(404);

		$pledges = $pledges->withOwner()->latest()->forStreamer($id);

		if ($request->has('limit'))
			$pledges->limit($request->get('limit'),$request->get('page'));
		else
			$pledges->limit(10);

		$pledges = $pledges->all();

		if ($request->ajax())
			return $response->make([
				'pledges' => $pledges->map(function($pledge) use ($guru) {
					return [
						'id' => $pledge->id,
						'amount' => sprintf('$%0.2f',$pledge->amount),
						'type' => $guru->type($pledge->type),
						'message' => htmlspecialchars($pledge->message),
						'win_limit' => $pledge->win_limit,
						'spending_limit' => $pledge->spending_limit,
						'times_donated' => $pledge->times_donated,
						'end_date' => ($pledge->end_date) ? $pledge->end_date->diffForHumans() : null,
						'created_at' => $pledge->created_at->diffForHumans(),
						'user' => [
							'id' => $pledge->owner->id,
							'username' => $pledge->owner->username,
						],
					];
				})
			]);
		else
			return $view->make('streamers.pledges.index')->with(compact('streamer','pledges'));
	}

}
