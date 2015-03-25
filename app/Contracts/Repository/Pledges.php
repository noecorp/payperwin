<?php namespace App\Contracts\Repository;

interface Pledges extends RepositoryContract {

	public function withStreamer();

	public function withOwner();

	public function forStreamer($streamerId);

	public function fromUser($userId);

	public function isRunning();
	
}