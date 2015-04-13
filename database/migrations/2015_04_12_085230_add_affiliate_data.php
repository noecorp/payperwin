<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAffiliateData extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->integer('referred_by')->unsigned()->nullable();
			$table->smallInteger('referrals')->unsigned()->default(0);
			$table->decimal('commission',4,2)->default(5.0);
			$table->boolean('referral_completed')->default(false);
			$table->boolean('streamer_completed')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('referred_by');
		});
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('referrals');
		});
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('commission');
		});
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('referral_completed');
		});
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('streamer_completed');
		});
	}

}
