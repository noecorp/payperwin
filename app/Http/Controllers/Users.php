<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Users as UsersRepository;
use App\Contracts\Repository\Pledges;
use App\Http\Requests\UpdateUser;
use Illuminate\Contracts\View\Factory as View;

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

		$pledges = $pledges->withStreamer()->latest()->limit(10)->fromUser($user->id)->all();

		return $this->view->make('users.show')->with(compact('user','pledges'));
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
 	 * @param  Redirect  $redirect
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function update(UpdateUser $request, Redirect $redirect, $id)
	{
		$user = $this->users->update($id, $request->all());

		return $redirect->back()->withSuccess('Done!');
	}

}
