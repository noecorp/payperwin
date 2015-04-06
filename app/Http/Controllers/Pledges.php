<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repository\Pledges as PledgesRepository;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;
use App\Http\Requests\CreatePledge;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use Carbon\Carbon;

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
		$this->middleware('own.pledge',['only'=>['edit','update']]);
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
	 * Store a newly created resource in storage.
	 *
	 * @param CreatePledge $request
	 * @param Response $response
	 *
	 * @return Response
	 */
	public function store(CreatePledge $request, Response $response)
	{	
		$pledge = $this->pledges->create($request->all());

		return $response->make(['id'=>$pledge->id], 201);
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
		$pledge = $this->pledges->find($id);

		if (!$pledge) return abort(404);

		$this->pledges->update($pledge,$request->all());

		return $this->redirect->back()->withSuccess('Done!');
	}

}
