<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdatePledge extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'amount' => 'numeric|min:0.01|max:10',
		];
	}

}
