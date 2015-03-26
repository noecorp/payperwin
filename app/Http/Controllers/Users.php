<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Users as UsersRepository;
use App\Contracts\Repository\Pledges;
use App\Http\Requests\UpdateUser;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class Users extends Controller {

	/**
	 * The Users Repository implementation.
	 *
	 * @var UsersRepository
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
	 * @param  UsersRepository  $users
	 * @param  View  $view
	 *
	 * @return void
	 */
	public function __construct(UsersRepository $users, View $view)
	{
		$this->users = $users;
		$this->view = $view;

		$this->middleware('auth',['except'=>['show']]);
		$this->middleware('own.user', ['except' => 'show']);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Pledges  $pledges
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Pledges $pledges, $id)
	{
		$user = $this->users->find($id);

		$feed = $pledges->withStreamer()->latest()->limit(10)->fromUser($id)->all();

		$average = round($pledges->fromUser($id)->averageAmount(),2);
		$highestPledge = $pledges->withOwner()->forStreamer($id)->orderingByAmount()->find();

		$stats = compact('average','highestPledge');

		return $this->view->make('users.show')->with(compact('user','feed','stats'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$user = $this->users->find($id);

		return $this->view->make('users.edit')->with(compact('user'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param UpdateUser $request
	 * @param Response $response
 	 * @param  Redirect  $redirect
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function update(UpdateUser $request, Response $response, Redirect $redirect, $id)
	{
		$user = $this->users->update($id, $request->all());

		if ($request->ajax())
		{
			return $response->json(['']);
		}
		else
		{
			return $redirect->back()->withSuccess('Done!');
		}
	}

}
