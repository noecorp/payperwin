<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePledgesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pledges', function(Blueprint $table)
		{
			$table->increments('id');

			$table->decimal('amount',10,2);
			$table->tinyInteger('type');
			$table->string('message',256)->nullable();

			$table->tinyInteger('game_limit');
			$table->decimal('sum_limit',12,2);

			$table->integer('user_id')->unsigned();
			$table->integer('streamer_id')->unsigned();

			$table->boolean('running')->default(1);
			$table->timestamp('end_date')->nullable();
			
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
		Schema::drop('pledges');
	}

}
