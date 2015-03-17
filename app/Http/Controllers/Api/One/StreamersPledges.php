<?php namespace App\Http\Controllers\Api\One;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Criteria\Pledges\Since;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class StreamersPledges extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @param Users $users
	 * @param Pledges $pledges
	 * @param Since $since
	 * @param Request $request
	 * @param Response $response
	 * @param int $streamerId
	 *
	 * @return Response
	 */
	public function index(Users $users, Pledges $pledges, Since $since, Request $request, Response $response, $streamingUsername)
	{
		$streamer = $users->havingStreamingUsername($streamingUsername)->find();

		if (!$streamer)
		{
			return $response->jsonNotFound();
		}

		$pledges = $pledges->latest()->after($request->get('after'))->forStreamer($streamer->id)->limit(10)->all()->map(function($pledge) {
			return [
				'id' => $pledge->id,
				'amount' => $pledge->amount,
				'message' => $pledge->message,
				'end_date' => $pledge->end_date,
				'game_limit' => $pledge->game_limit,
				'sum_limit' => $pledge->sum_limit,
				'user' => $pledge->user->username,
				'running' => $pledge->running,
				'created_at' => $pledge->created_at
			];
		});

		return $response->json(compact('pledges'));
	}

}
