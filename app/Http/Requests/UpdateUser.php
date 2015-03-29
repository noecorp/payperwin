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
			'email' => 'email|max:254|unique:users',
			'password' => 'confirmed|min:8',
			'streamer' => 'in:1,0',
			'summoner_id' => 'integer',
			'summoner_name' => 'max:64|min:1',
			'region' => 'in:br,eune,euw,kr,lan,las,na,oce,ru,tr',
		];
	}

}
