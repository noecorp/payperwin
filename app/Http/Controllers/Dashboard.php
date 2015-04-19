<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory as View;
use Illuminate\Contracts\Auth\Guard;

class Dashboard extends Controller {

	/**
	 * View factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Authentication implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new controller instance.
	 *
	 * @param View $view
	 * @param Guard $auth
	 *
	 * @return void
	 */
	public function __construct(View $view, Guard $auth)
	{
		$this->view = $view;
		$this->auth = $auth;
		
		$this->middleware('auth');
		$this->middleware('start');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return $this->view->make('dashboard.index');
	}

}
