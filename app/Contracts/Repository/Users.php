<?php namespace App\Contracts\Repository;

interface Users extends RepositoryContract {
	
	public function createWithFacebook(array $data);

	public function createWithTwitch(array $data);

	public function withFacebookId($id);

	public function withTwitchId($id);

	public function withUsername($username);

}