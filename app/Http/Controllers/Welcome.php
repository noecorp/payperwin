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
	 * Redirector implementation.
	 *
	 * @var Redirect
	 */
	protected $redirect;

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
	 * @param Redirect $redirect
	 * @param Guard $auth
	 *
	 * @return void
	 */
	public function __construct(View $view, Redirect $redirect, Guard $auth)
	{
		$this->view = $view;
		$this->redirect = $redirect;
		$this->auth = $auth;
		
		$this->middleware('auth',['only'=>'start']);
	}

	/**
	 * Show the application welcome screen to the visitor.
	 *
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function index()
	{
		if ($this->auth->user())
		{
			return $this->redirect->to('dashboard');
		}

		return $this->view->make('welcome.index');
	}

	/**
	 * Show the getting started screen to the newly-registered user.
	 *
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function start()
	{
		if ($this->auth->user()->start_completed)
		{
			return $this->redirect->to('dashboard');
		}

		$display = 'both';

		if ($this->auth->user()->streamer && ($this->auth->user()->twitch_id || $this->auth->user()->summoner_id))
		{
			$display = 'streamer';
		}

		if ($this->auth->user()->funds > 0)
		{
			$display = ($display == 'streamer') ? 'both' : 'fan';
		}

		return $this->view->make('welcome.start', compact('display'));
	}

}
