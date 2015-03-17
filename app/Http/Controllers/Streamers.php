<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory as View;
use App\Contracts\Repository\Users;

class Streamers extends Controller {

	/**
	 * The Users Repository implementation.
	 *
	 * @var Users
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
	 * @param  Users  $users
	 * @param  View  $view
	 *
	 * @return void
	 */
	public function __construct(Users $users, View $view)
	{
		$this->users = $users;
		$this->view = $view;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return $this->view->make('streamers.index');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$streamer = $this->users->isStreamer()->find($id);
		
		return $this->view->make('streamers.show')->with(compact('streamer'));
	}

}
