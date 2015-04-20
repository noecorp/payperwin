<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Contracts\Service\Gurus\Transaction as Guru;

class AddTransactionsUsernameColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$guru = app(Guru::class);

		Schema::table('transactions', function(Blueprint $table)
		{
			$table->string('username', 100)->nullable();
		});

		$transactions = DB::table('transactions')
		->select('transactions.id','users.username')
		->join('pledges','pledges.id','=','transactions.pledge_id')
		->join('users','users.id','=','pledges.streamer_id')
		->where('transaction_type',$guru->pledgeTaken())
		->get();

		foreach ($transactions as $transaction)
		{
			DB::table('transactions')->where('id',$transaction->id)->update(['username'=>$transaction->username]);
		}

		$transactions = DB::table('transactions')
		->select('transactions.id','users.username')
		->join('pledges','pledges.id','=','transactions.pledge_id')
		->join('users','users.id','=','pledges.user_id')
		->where('transaction_type',$guru->pledgePaid())
		->get();

		foreach ($transactions as $transaction)
		{
			DB::table('transactions')->where('id',$transaction->id)->update(['username'=>$transaction->username]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->dropColumn('username');
		});
	}

}
