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
			$table->string('short_url',32)->nullable();
			$table->boolean('live')->default(0);

			$table->string('email', 254)->nullable()->unique();

			//If logging in through a social provider, there's no password. It
			//can be set after.
			$table->string('password', 60)->nullable();

			$table->string('avatar',32)->nullable();

			$table->boolean('streamer')->default(0);

			$table->decimal('funds',6,2)->default(0);
			$table->decimal('earnings',8,2)->default(0);

			$table->bigInteger('summoner_id')->unsigned()->nullable();
			$table->string('summoner_name',64)->nullable();
			$table->string('region',10)->nullable();

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
