<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class AddUniqueMatchesUserConstraint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// This migration is before launching proper, so we can safely remove duplicates
		// like this.

		$matches = DB::table('matches')->orderBy('id','asc')->get();

		$existing = [];

		foreach ($matches as $match)
		{
			if (isset($existing[$match->server_match_id . '-' . $match->user_id]))
			{
				DB::table('matches')->where('id',$match->id)->delete();
			}
			else
			{
				$existing[$match->server_match_id . '-' . $match->user_id] = true;
			}
		}

		Schema::table('matches', function(Blueprint $table)
		{
			$table->unique(['server_match_id','user_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('matches', function(Blueprint $table)
		{
			$table->dropUnique(['server_match_id','user_id']);
		});
	}

}
