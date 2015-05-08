<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Contracts\Auth\Guard;

class SubmitPayout extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @param Guard $auth
	 *
	 * @return array
	 */
	public function rules(Guard $auth)
	{
		return [
			'amount' => 'required|numeric|min:100|max:'.$auth->user()->earnings,
			'email' => 'required|email',
		];
	}

}
