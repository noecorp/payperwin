<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class Register extends Request {
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'username' => 'required|max:100',
			'email' => 'required|email|max:254|unique:users',
			'password' => 'required|confirmed|min:8',
		];
	}

}
