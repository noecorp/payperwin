<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Users;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\View\Factory as View;
use App\Http\Requests\CreateDeposit;
use Illuminate\Contracts\Auth\Guard;
use App\Contracts\Service\Acidifier as AcidifierInterface;

class Deposits extends Controller {

	/**
	 * Create a new pledges controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the form for creating a new resource.
	 *
 	 * @param  View  $view
 	 *
	 * @return Response
	 */
	public function create(View $view)
	{
		return $view->make('deposits.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param CreateDeposit $request
	 * @param Guard $auth
	 * @param  AcidifierInterface  $acid
	 * @param  Users  $users
	 * @param Redirect $redirect
	 *
	 * @return Response
	 */
	public function store(CreateDeposit $request, Guard $auth, AcidifierInterface $acid, Users $users, Redirect $redirect)
	{
		$acid->transaction(function() use ($request, $auth, $users)
		{
			$users->update($auth->user(), ['funds'=>$auth->user()->funds + $request->get('amount')]);
		});

		return $redirect->back()->withSuccess('Done!');
	}

}
