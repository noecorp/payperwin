<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use App\Contracts\Service\Gurus\Role as RoleGuru;

class CreatePermissionsTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('permission_user');
		Schema::create('permission_user', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('permission_id');
			$table->unsignedInteger('user_id')->index();

			$table->timestamps();
		});

		Schema::dropIfExists('role_user');
		Schema::create('role_user', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('role_id');
			$table->unsignedInteger('user_id')->index();

			$table->timestamps();
		});

		$roleGuru = app(RoleGuru::class);

		$user = DB::table('users')->orderBy('id','asc')->first();

		if ($user)
		{
			DB::table('role_user')->insert(['role_id' => $roleGuru->admin()['id'], 'user_id' => $user->id, 'updated_at' => Carbon::now(), 'created_at' => Carbon::now()]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permission_user');
		Schema::drop('role_user');
	}

}
