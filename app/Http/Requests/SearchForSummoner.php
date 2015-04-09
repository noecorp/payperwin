<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Contracts\Service\Gurus\Region as RegionGuru;

class SearchForSummoner extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @param RegionGuru $guru
	 *
	 * @return array
	 */
	public function rules(RegionGuru $guru)
	{
		return [
			'region' => 'required|in:'.implode(',', $guru->regions()),
			'summoner_name' => 'required'
		];
	}

}
