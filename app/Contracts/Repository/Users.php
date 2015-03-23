<?php namespace App\Contracts\Repository;

interface Users extends RepositoryContract {
	
	public function createWithFacebook(array $data);

	public function createWithTwitch(array $data);

	public function havingFacebookId($id);

	public function havingTwitchId($id);

	public function havingUsername($username);

	public function havingStreamingUsername($streamingUsername);

	public function isStreamer();

	public function withPledges();

	public function hasTwitchId();

	public function hasSummonerId();

	public function withLatestMatch();

}