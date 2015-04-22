<?php namespace App\Contracts\Repository;

interface Pledges extends RepositoryContract {

	public function withStreamer();

	public function withOwner();

	public function forStreamer($streamerId);

	public function fromUser($userId);

	public function isRunning();
	
	public function orderingByAmount($highest = true);

	public function mostSpent();

	public function countPledgers();

	public function today();

	public function thisMonth();

	public function thisWeek();
	
}
