<?php namespace App\Repositories;

use App\Contracts\Repository\Users as UsersRepository;
use App\Models\User;

class Users extends AbstractRepository implements UsersRepository {
	
	/**
	 * {@inheritdoc}
	 *
	 * @param \App\Models\User $user
	 */
	public function __construct(User $user)
	{
		parent::__construct($user);
	}

	public function createWithFacebook(array $data)
	{
		$fields = [
			'name' => $data['name'],
			'email' => $data['email'],
			'facebook_id' => $data['facebook_id'],
		];

		return $this->create($fields);
	}

	public function createWithTwitch(array $data)
	{
		$fields = [
			'name' => $data['name'],
			'username' => $data['username'],
			'email' => $data['email'],
			'twitch_id' => $data['twitch_id'],
			'twitch_username' => $data['username'],
		];

		return $this->create($fields);
	}

	public function havingFacebookId($id)
	{
		return $this->newQuery()->whereFacebookId($id)->first();
	}

	public function havingTwitchId($id)
	{
		return $this->newQuery()->whereTwitchId($id)->first();
	}

	public function havingUsername($username)
	{
		return $this->newQuery()->whereUsername($username)->first();
	}

}