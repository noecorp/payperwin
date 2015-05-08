<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultCommission extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users',function(Blueprint $table)
		{
			$table->decimal('commission',4,2)->default(3.5)->change();
		});

		// Before launching, this is safe.
		DB::table('users')->update(['commission' => 3.5]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users',function(Blueprint $table)
		{
			$table->decimal('commission',4,2)->default(5.0)->change();
		});
	}

}
