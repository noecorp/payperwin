<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');

			//Completely optional
			$table->string('name', 100)->nullable();

			//Username can be not set if logging in through Facebook
			$table->string('username', 100)->nullable();

			$table->bigInteger('facebook_id')->unsigned()->nullable()->unique();
			$table->bigInteger('twitch_id')->unsigned()->nullable()->unique();
			$table->string('twitch_username',25)->nullable();

			//Again, email can be missing when logging in through a social provider.
			//It would have be set in the profile and the account would be locked without it.
			$table->string('email', 254)->nullable()->unique();

			//Again, if logging in through a social provider, there's no password. It
			//can be set after.
			$table->string('password', 60)->nullable();

			$table->boolean('streamer')->default(0);
			$table->string('streaming_username',25)->nullable()->unique();

			$table->decimal('funds',12,2)->default(0);
			$table->decimal('earnings',12,2)->default(0);

			$table->rememberToken();
			
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
