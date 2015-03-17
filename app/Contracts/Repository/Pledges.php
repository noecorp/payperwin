<?php namespace App\Contracts\Repository;

interface Pledges extends RepositoryContract {

	public function withStreamer();

	public function withUser();

	public function forStreamer($streamerId);

	public function fromUser($userId);
	
}