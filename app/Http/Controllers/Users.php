<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Users as UsersRepository;
use App\Http\Requests\UpdateUser;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use App\Contracts\Service\Gurus\Region as RegionGuru;

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

		$this->middleware('auth');
		$this->middleware('own.user');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param RegionGuru $guru
	 * @param int $id
	 *
	 * @return Response
	 */
	public function edit(RegionGuru $guru, $id)
	{
		$user = $this->users->find($id);

		if (!$user) return abort(404);

		return $this->view->make('users.edit')->with(compact('user','guru'));
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
		$user = $this->users->find($id);

		if (!$user) return abort(404);

		$this->users->update($user, $request->all());

		if ($request->ajax())
		{
			return $response->json(['success'=>true]);
		}
		else
		{
			return $redirect->back()->withSuccess('Done!');
		}
	}

}
