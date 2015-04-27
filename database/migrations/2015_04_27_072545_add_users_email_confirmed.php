<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsersEmailConfirmed extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->boolean('email_confirmed')->default(false);
			$table->string('confirmation_code', 8)->nullable();
		});

		$users = DB::table('users')->get();

		foreach ($users as $user)
		{
			$data = ['confirmation_code' => str_random(8)];

			if ($user->twitch_id)
			{
				$data['email_confirmed'] = true;
			}

			DB::table('users')->where('id', $user->id)->update($data);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('confirmation_code');
		});

		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('email_confirmed');
		});
	}

}
