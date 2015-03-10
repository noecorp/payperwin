<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Contracts\Service\PledgeGuru;

class CreatePledge extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @param PledgeGuru $guru
	 *
	 * @return array
	 */
	public function rules(PledgeGuru $guru)
	{
		return [
			'amount' => 'numeric|min:0.01|max:10',
			'type' => 'in:'.implode(',', $guru->types()),
		];
	}

}
