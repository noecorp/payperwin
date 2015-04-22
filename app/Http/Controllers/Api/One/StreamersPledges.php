<?php namespace App\Http\Controllers\Api\One;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Users;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Factory as Validator;

class StreamersPledges extends Controller {

	public function __construct()
	{
		$this->middleware('json');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param Users $users
	 * @param Pledges $pledges
	 * @param Request $request
	 * @param Response $response
	 * @param int $streamerId
	 *
	 * @return Response
	 */
	public function index(Validator $validator, Users $users, Pledges $pledges, Request $request, Response $response, $twitchUsername)
	{
		$validation = $validator->make($request->all(), [
			'after' => 'integer',
			'limit' => 'integer|min:1|max:500',
			'page' => 'integer|min:1',
		]);

		if ($validation->fails())
		{
			$failed = $validation->failed();
			$reason = [];
			foreach ($failed as $key=>$value)
			{
				foreach ($value as $k=>$v)
				{
					$reason[$key] = strtolower($k);
					break;
				}
			}
			abort(400,json_encode($reason));
		}

		$streamer = $users->isStreamer()->havingTwitchUsername($twitchUsername)->hasSummonerId()->hasTwitchId()->find();

		if (!$streamer)
		{
			return abort(404);
		}

		$pledges = $pledges->withOwner()->latest()->forStreamer($streamer->id);

		if ($request->has('after'))
		{
			$pledges->after(Carbon::createFromTimestamp($request->get('after')));
		}

		if ($request->has('limit'))
		{
			if ($request->has('page'))
			{
				$pledges->limit($request->get('limit'), $request->get('page'));
			}
			else
			{
				$pledges->limit($request->get('limit'));
			}
		}
		else
		{
			if ($request->has('page'))
			{
				$pledges->limit(50, $request->get('page'));
			}
			else
			{
				$pledges->limit(50);
			}
		}

		$pledges = $pledges->all()->map(function($pledge) {
			return [
				'id' => $pledge->id,
				'amount' => $pledge->amount,
				'message' => $pledge->message,
				'end_date' => ($pledge->end_date) ? $pledge->end_date->timestamp : null,
				'win_limit' => $pledge->win_limit,
				'spending_limit' => $pledge->spending_limit,
				'user' => $pledge->owner->username,
				'running' => $pledge->running,
				'created_at' => $pledge->created_at->timestamp
			];
		});

		return $response->json(compact('pledges'));
	}

}
