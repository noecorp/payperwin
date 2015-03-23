<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table)
		{
			$table->bigIncrements('id');

			$table->integer('user_id')->unsigned();

			$table->tinyInteger('transaction_type')->unsigned();

			$table->tinyInteger('source')->unsigned();

			$table->integer('pledge_id')->unsigned()->nullable();

			$table->string('reference',64)->nullable();

			$table->decimal('amount',8,2);

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
		Schema::drop('transactions');
	}

}
