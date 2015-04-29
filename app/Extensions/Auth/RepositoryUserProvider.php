<?php namespace App\Extensions\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Auth\Authenticatable;

class RepositoryUserProvider implements UserProvider {

	/**
	 * The Users Repository instance.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Hasher instance.
	 *
	 * @var Hasher
	 */
	protected $hasher;

	/**
	 * Create a new Repository User Provider instance.
	 *
	 * @param Users $users
	 * @param Hasher $hasher
	 */
	public function __construct(Users $users, Hasher $hasher)
	{
		$this->users = $users;
		$this->hasher = $hasher;
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieveById($identifier)
	{
		return $this->users->find($identifier);
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieveByToken($identifier, $token)
	{
		return $this->users->havingRememberToken($token)->find();
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateRememberToken(Authenticatable $user, $token)
	{
		return $this->users->update($user, ['remember_token' => $token]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieveByCredentials(array $credentials)
	{
		$credentials['password'] = $this->hasher->make($credentials['password']);

		return $this->users->havingCredentials($credentials)->find();
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		$plain = $credentials['password'];

		return $this->hasher->check($plain, $user->getAuthPassword());
	}

}
