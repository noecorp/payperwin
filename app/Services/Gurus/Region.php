<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Region as RegionGuruInterface;

class Region implements RegionGuruInterface {

	/**
	 * {@inheritdoc}
	 */
	public function regions()
	{
		return [
			'na',
			'euw',
			'eune',
			'oce',
			'br',
			'kr',
			'lan',
			'las',
			'tr',
			'ru',
		];
	}

}