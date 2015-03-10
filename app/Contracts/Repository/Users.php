<?php namespace App\Contracts\Repository;

interface Users extends RepositoryContract {
	
	public function createWithFacebook(array $data);

	public function createWithTwitch(array $data);

	public function havingFacebookId($id);

	public function havingTwitchId($id);

	public function havingUsername($username);

}