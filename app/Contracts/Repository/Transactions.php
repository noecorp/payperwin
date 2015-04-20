<?php namespace App\Contracts\Repository;

interface Transactions extends RepositoryContract {

	public function forUser($id);
	
}
