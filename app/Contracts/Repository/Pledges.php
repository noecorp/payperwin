<?php namespace App\Contracts\Repository;

interface Pledges extends RepositoryContract {

	public function havingIdWithStreamer($id);

	public function havingIdWithUserAndStreamer($id);

	public function latestWithUsersAndStreamers($take, $page = null);

	public function latestForStreamerWithUsers($streamerId, $take, $page = null);

	public function latestForUserWithStreamers($userId, $take, $page = null);
	
}