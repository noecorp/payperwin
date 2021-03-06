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

			$table->decimal('amount',5,2);
			$table->tinyInteger('type');
			$table->string('message',256);

			$table->tinyInteger('win_limit')->nullable();
			$table->decimal('spending_limit',6,2)->nullable();

			$table->integer('user_id')->unsigned();
			$table->integer('streamer_id')->unsigned();

			$table->boolean('running')->default(1);
			$table->timestamp('end_date')->nullable();
			$table->smallInteger('times_donated')->unsigned()->default(0);
			
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
