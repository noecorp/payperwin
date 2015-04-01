<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateDeposit extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'amount' => 'required|numeric|min:0.01|max:500',
		];
	}

}
