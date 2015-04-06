<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Contracts\Service\Gurus\Pledge as PledgeGuru;
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
			'amount' => 'required|numeric|min:0.01|max:'.$auth->user()->funds,
			'type' => 'required|in:'.implode(',', $guru->types()),
			'streamer_id' => 'required|integer|min:1|exists:users,id,streamer,1',
			'message' => 'max:256',
			'spending_limit' => 'numeric|min:'.$this->get('amount').'|max:9999.99|',
			'win_limit' => 'integer|min:1|max:255',
			'end_date' => 'date_format:d-m-Y',
			'user_id' => 'in:'.$auth->user()->id
		];
	}

}
