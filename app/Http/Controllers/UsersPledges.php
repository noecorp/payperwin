<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;

class UsersPledges extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @param Users $users
	 * @param Pledges $pledges
	 * @param View $view
	 * @param int $userId
	 *
	 * @return Response
	 */
	public function index(Users $users, Pledges $pledges, View $view, $userId)
	{
		$user = $users->find($userId);

		$pledges = $pledges->withStreamer()->latest()->limit(10)->fromUser($userId);

		return $view->make('users.pledges.index')->with(compact('user','pledges'));
	}

}
