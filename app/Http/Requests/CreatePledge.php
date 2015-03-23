<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Contracts\Service\PledgeGuru;
use Illuminate\Contracts\Auth\Guard;

class CreatePledge extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @param PledgeGuru $guru
	 * @param Guard $auth
	 *
	 * @return array
	 */
	public function rules(PledgeGuru $guru, Guard $auth)
	{
		return [
			'amount' => 'required|numeric|min:0.01|max:999.99',
			'type' => 'required|in:'.implode(',', $guru->types()),
			'streamer_id' => 'required|integer|min:1|exists:users,id,streamer,1',
			'message' => 'max:256',
			'sum_limit' => 'numeric|min:0.01|max:9999.99',
			'game_limit' => 'integer|min:1|max:255',
			'end_date' => 'date',
			'user_id' => 'in:'.$auth->user()->id
		];
	}

}
