<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Contracts\Service\PledgeKeeper;

class CreatePledge extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @param PledgeKeeper $keeper
	 *
	 * @return array
	 */
	public function rules(PledgeKeeper $keeper)
	{
		return [
			'amount' => 'numeric|min:0.01|max:10',
			'type' => 'in:'.implode(',', $keeper->types()),
		];
	}

}
