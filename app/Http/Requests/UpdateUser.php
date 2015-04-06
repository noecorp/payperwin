<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateUser extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'username' => 'max:100',
			'email' => 'email|max:254|unique:users,email,'.$this->get('_user_id'),
			'password' => 'min:8|confirmed',
			'streamer' => 'in:1,0',
			'summoner_id' => 'integer',
			'summoner_name' => 'min:1|max:64',
			'region' => 'in:br,eune,euw,kr,lan,las,na,oce,ru,tr',
		];
	}

}
