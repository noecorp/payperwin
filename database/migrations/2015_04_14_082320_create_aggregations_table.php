<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('aggregations', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('user_id')->unsigned();

			$table->decimal('amount',12,2)->unsigned();

			$table->tinyInteger('type')->unsigned();
			$table->tinyInteger('reason')->unsigned();

			$table->tinyInteger('day')->unsigned();
			$table->tinyInteger('week')->unsigned();
			$table->tinyInteger('month')->unsigned();
			$table->tinyInteger('year')->unsigned(); // 15 = 2015

			$table->timestamps();

			$table->unique(['user_id','type','reason','day','week','month','year'], 'aggregations_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('aggregations');
	}

}
