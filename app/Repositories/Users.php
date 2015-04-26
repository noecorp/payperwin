<?php namespace App\Repositories;

use App\Contracts\Repository\Users as UsersRepository;
use App\Models\User;
use App\Models\Model;
use App\Events\Repositories\UserWasCreated;
use App\Events\Repositories\UserWasUpdated;
use App\Events\Repositories\UsersWereCreated;
use App\Events\Repositories\UsersWereUpdated;

class Users extends AbstractRepository implements UsersRepository {
	
	/**
	 * {@inheritdoc}
	 */
	protected function model()
	{
		return User::class;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return UserWasCreated
	 */
	protected function eventForModelCreated(Model $model)
	{
		return new UserWasCreated($model);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return UsersWereCreated
	 */
	protected function eventForModelsCreated()
	{
		return new UsersWereCreated();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return UserWasUpdated
	 */
	protected function eventForModelUpdated(Model $model, array $changed)
	{
		return new UserWasUpdated($model, $changed);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return UsersWereUpdated
	 */
	protected function eventForModelsUpdated()
	{
		return new UsersWereUpdated();
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
		$this->query()->whereFacebookId($id);
		
		return $this;
	}

	public function havingTwitchId($id)
	{
		$this->query()->whereTwitchId($id);

		return $this;
	}

	public function havingUsername($username)
	{
		$this->query()->whereUsername($username);

		return $this;
	}

	public function havingTwitchUsername($twitchUsername)
	{
		$this->query()->whereTwitchUsername($twitchUsername);

		return $this;
	}

	public function havingCredentials(array $credentials)
	{
		$this->query()->where('email', $credentials['email']);

		return $this;
	}

	public function havingRememberToken($token)
	{
		$this->query()->where('remember_token', $token);

		return $this;
	}

	public function isStreamer()
	{
		$this->query()->whereStreamer(1);

		return $this;
	}

	public function isLive()
	{
		$this->query()->whereLive(1);

		return $this;
	}

	public function withPledges()
	{
		$this->query()->with('pledges');
		
		return $this;
	}

	public function hasTwitchId()
	{
		$this->query()->whereNotNull('twitch_id');

		return $this;
	}

	public function hasSummonerId()
	{
		$this->query()->whereNotNull('summoner_id');

		return $this;
	}

	public function withLatestMatch()
	{
		$this->query()->with(['matches' => function($query)
		{
			$query->orderBy('match_date','desc')->limit(1);
		}]);

		return $this;
	}

}
