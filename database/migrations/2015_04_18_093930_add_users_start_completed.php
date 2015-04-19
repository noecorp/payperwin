<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUsersStartCompleted extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->boolean('start_completed')->default(false);
		});

		$users = DB::table('users')->get();
		foreach ($users as $user)
		{
			if ($user->streamer)
			{
				if ($user->streamer_completed)
				{
					DB::table('users')->where('id',$user->id)->update(['start_completed'=>true]);
				}
			}
			else
			{
				$pledges = DB::table('pledges')->where('user_id', $user->id)->count();
				if ($pledges)
				{
					DB::table('users')->where('id',$user->id)->update(['start_completed'=>true]);
				}
			}
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
			$table->dropColumn('start_completed');
		});
	}

}
