<?php namespace App\Repositories;

use App\Contracts\Repository\Users as UsersRepository;
use App\Models\User;

class Users implements UsersRepository {
	
	/**
	 * {@inheritdoc}
	 *
	 * @return User
	 */
	public function create(array $data)
	{
		return User::create($data);
	}

	public function createWithFacebook(array $data)
	{
		$fields = [
			'name' => $data['name'],
			'email' => $data['email'],
			'facebook_id' => $data['facebook_id'],
		];

		return User::create($fields);
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

		return User::create($fields);
	}

	public function withId($id)
	{
		return User::find($id);
	}

	public function withFacebookId($id)
	{
		return User::whereFacebookId($id)->first();
	}

	public function withTwitchId($id)
	{
		return User::whereTwitchId($id)->first();
	}

	public function withUsername($username)
	{
		return User::whereUsername($username)->first();
	}

}