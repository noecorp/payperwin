<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        User::create([
        	'email' => 'foo@bar.com',
        	'username' => 'foo',
        	'twitch_id' => 1,
        	'twitch_username' => 'alexich',
        	'summoner_id' => 65009177,
        	'region' => 'na',
        	'funds' => 1000,
        	'streamer' => 1,
        ]);

        User::create([
        	'email' => 'bar@foo.com',
        	'username' => 'bar',
        	'twitch_id' => 2,
        	'twitch_username' => 'imaqtpie',
        	'summoner_id' => 1,
        	'region' => 'na',
        	'funds' => 1000,
        	'streamer' => 1,
        ]);
	}

}
