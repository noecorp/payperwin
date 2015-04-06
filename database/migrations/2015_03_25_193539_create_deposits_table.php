<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('deposits', function(Blueprint $table) {
			$table->engine = 'InnoDB';

			$table->increments('id');

			$table->integer('user_id')->unsigned();

			$table->string('payment_provider');

			$table->string('transaction_id',128);

			$table->string('parent_transaction_id')->nullable();

			//assuming USD
			$table->decimal('gross',12,2);

			$table->string('email');

			$table->decimal('fee', 12,2);

			$table->dateTime('payment_date');

			$table->string('status');

			$table->integer('status_code')->unsigned;

			$table->json('source_message');

			$table->boolean('processed')->default(false);

			$table->timestamps();


			$table->unique(['transaction_id','status']);
			$table->foreign('user_id')->references('id')->on('users');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('deposits');
	}

}
