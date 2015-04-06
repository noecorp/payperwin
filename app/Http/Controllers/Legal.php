<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory as View;

class Legal extends Controller {

	/**
	 * View Factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Create a new Legal controller instance.
	 *
	 * @param View $view
	 *
	 * @return void
	 */
	public function __construct(View $view)
	{
		$this->view = $view;
	}

	public function terms()
	{
		return $this->view->make('legal.terms');
	}

	public function privacy()
	{
		return $this->view->make('legal.privacy');
	}

}