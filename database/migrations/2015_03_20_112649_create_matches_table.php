<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matches', function(Blueprint $table)
		{
			$table->increments('id');

			$table->bigInteger('server_match_id')->unsigned();

			$table->integer('user_id')->unsigned();

			$table->boolean('win');

			$table->smallInteger('champion')->unsigned();

			$table->tinyInteger('kills')->unsigned();

			$table->tinyInteger('assists')->unsigned();

			$table->tinyInteger('deaths')->unsigned();			

			$table->timestamp('match_date');

			$table->boolean('settled')->default(0);
			
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
		Schema::drop('matches');
	}

}
