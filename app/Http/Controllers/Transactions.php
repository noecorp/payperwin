<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory as View;
use Illuminate\Contracts\Auth\Guard;
use App\Contracts\Repository\Transactions as TransactionsRepository;
use Illuminate\Http\Request;
use App\Contracts\Service\Gurus\Transaction as TransactionGuru;

class Transactions extends Controller {

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
	 * Transactions repository implementation.
	 *
	 * @var TransactionsRepository
	 */
	protected $transactions;

	/**
	 * Request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new controller instance.
	 *
	 * @param View $view
	 * @param Guard $auth
	 *
	 * @return void
	 */
	public function __construct(View $view, Guard $auth, TransactionsRepository $transactions, Request $request)
	{
		$this->view = $view;
		$this->auth = $auth;
		$this->transactions = $transactions;
		$this->request = $request;
		
		$this->middleware('auth');
	}

	/**
	 * Show the transactions index page.
	 *
	 * @return \Illuminate\View\View
	 */
	public function getIndex(TransactionGuru $guru)
	{
		$page = (int)$this->request->get('page');
		$page = ($page >= 1) ? $page : 1;

		$limit = 10;

		$transactions = $this->transactions->forUser($this->auth->user()->id)->latest()->limit($limit, $page)->all();

		$count = $this->transactions->forUser($this->auth->user()->id)->count();

		$more = ($count > $page * $limit);

		$less = ($page > 1);

		return $this->view->make('transactions.index', compact('transactions', 'guru', 'page', 'more', 'less'));
	}

}
