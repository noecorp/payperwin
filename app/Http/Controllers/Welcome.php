<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory as View;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\Auth\Guard;

class Welcome extends Controller {

	/**
	 * View factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(View $view)
	{
		$this->view = $view;
		
		$this->middleware('auth',['only'=>'start']);
	}

	/**
	 * Show the application welcome screen to the visitor.
	 *
	 * @param Guard $auth
	 * @param Redirect $redirect
	 *
	 * @return \Illuminate\View\View
	 */
	public function index(Guard $auth, Redirect $redirect)
	{
		if ($auth->user())
		{
			return $redirect->to('start');
		}

		return $this->view->make('welcome.index');
	}

	/**
	 * Show the getting started screen to the newly-registered user.
	 *
	 * @return \Illuminate\View\View
	 */
	public function start()
	{
		return $this->view->make('welcome.start');
	}

}
