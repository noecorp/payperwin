<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Contracts\Repository\Pledges as PledgesRepository;
use Illuminate\Contracts\View\Factory as View;
use App\Http\Requests\CreatePledge;
use App\Http\Requests\UpdatePledge;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class Pledges extends Controller {

	/**
	 * The Pledges Repository implementation.
	 *
	 * @var PledgesRepository
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
	 * @param  PledgesRepository  $pledges
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

		if (!$pledge) return abort(404);

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

		if (!$pledge) return abort(404);

		return $this->view->make('pledges.edit')->with(compact('pledge'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param UpdatePledge $request
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function update(UpdatePledge $request, $id)
	{
		$pledge = $this->pledges->find($id);

		if (!$pledge) return abort(404);

		$this->pledges->update($pledge,$request->all());

		return $this->redirect->back()->withSuccess('Done!');
	}

}
