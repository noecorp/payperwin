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
			$table->increments('id');

			$table->integer('user_id')->unsigned();

			$table->string('payment_provider',24);

			$table->string('transaction_id',128);

			$table->string('parent_transaction_id',32)->nullable();

			//assuming USD
			$table->decimal('gross',12,2);

			$table->string('email',254);

			$table->decimal('fee', 12,2);

			$table->dateTime('payment_date');

			$table->string('status',32);

			$table->integer('status_code')->unsigned;

			$table->json('source_message');

			$table->boolean('processed')->default(false);

			$table->timestamps();

			$table->unique(['transaction_id','status']);
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
