<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class Login extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'email' => 'required|email',
			'password' => 'required',
		];
	}

}
