<?php namespace App\Contracts\Repository;

interface Matches extends RepositoryContract {

	public function forStreamer($id);

	public function orderingByMatchDate($latest = true);

	public function isUnsettled();
	
}
