<?php

use Illuminate\Database\Seeder;
use App\Models\Pledge;
use App\Services\Gurus\Pledge as PledgeGuru;

class PledgesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$guru = new PledgeGuru;

		Pledge::create([
			'user_id' => 2,
			'amount' => '0.01',
			'type' => $guru->win(),
			'streamer_id' => 1,
			'message' => ''
		]);
	}

}
