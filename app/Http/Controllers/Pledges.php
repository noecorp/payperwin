<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repository\Pledges as PledgesRepository;
use Illuminate\Contracts\View\Factory as View;

class Pledges extends Controller {

	protected $pledges;

	public function __construct(PledgesRepository $pledges, View $view)
	{
		$this->pledges = $pledges;
		$this->view = $view;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$pledges = $this->pledges->latestWithUsersAndStreamers(10);

		return $this->view->make('pledges.index')->with(compact('pledges'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return $this->view->make('pledges.new');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \App\Requests\CreatePledge $request
	 *
	 * @return Response
	 */
	public function store(\App\Requests\CreatePledge $request)
	{
		$pledge = $this->pledges->create($request->all());
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
		//
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
		$pledge = $this->pledges->havingIdWithStreamer($id);
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
		$this->pledges->update($id,$request->all());
	}

}
