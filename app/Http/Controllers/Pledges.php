<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;
use App\Http\Requests\CreatePledge;
use Illuminate\Routing\Redirector as Redirect;
use App\Services\PledgeGuru;

class Pledges extends Controller {

	/**
	 * The Pledges Repository implementation.
	 *
	 * @var UsersRepository
	 */
	protected $pledges;

	/**
	 * The View Factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * The Redirector implementation.
	 *
	 * @var Redirect
	 */
	protected $redirect;

	/**
	 * Create a new pledges controller instance.
	 *
	 * @param  PledgesRepository  $users
	 * @param  View  $view
	 * @param Redirect $redirect
	 *
	 * @return void
	 */
	public function __construct(PledgesRepository $pledges, View $view, Redirect $redirect)
	{
		$this->pledges = $pledges;
		$this->view = $view;
		$this->redirect = $redirect;

		$this->middleware('auth',['except'=>['index','show']]);
		$this->middleware('own.pledge',['except'=>['index','show']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$pledges = $this->pledges->withOwner()->withStreamer()->latest()->limit(10)->all();

		return $this->view->make('pledges.index')->with(compact('pledges'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param Users $users
	 * @param Request $request
	 * @param PledgeGuru $guru
	 *
	 * @return Response
	 */
	public function create(Users $users, Request $request, PledgeGuru $guru)
	{
		$streamerId = $request->get('streamerId');

		$streamer = ($streamerId) ? $users->isStreamer()->find($id) : null;

		return $this->view->make('pledges.create')->with(compact('streamer','guru'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param CreatePledge $request
	 *
	 * @return Response
	 */
	public function store(CreatePledge $request)
	{
		$pledge = $this->pledges->create($request->all());

		return $this->redirect->to('pledges/'.$pledge->id);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function show($id)
	{
		$pledge = $this->pledges->withOwner()->withStreamer()->find($id);

		return $this->view->make('pledges.show')->with(compact('pledge'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function edit($id)
	{
		$pledge = $this->pledges->withStreamer()->find($id);

		return $this->view->make('pledges.edit')->with(compact('pledge'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \App\Requests\UpdatePledge $request
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function update(\App\Requests\UpdatePledge $request, $id)
	{
		$pledge = $this->pledges->update($id,$request->all());

		return $this->redirect->back()->withSuccess('Done!');
	}

}
