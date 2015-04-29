<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Contracts\Models\Permissible as PermissibleContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, PermissibleContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'username', 'email', 'email_confirmed', 'confirmation_code', 'newsletter_enabled',
		'twitch_id', 'facebook_id', 'twitch_username', 'live',
		'password',
		'streamer', 'summoner_id', 'summoner_name', 'region', 'streamer_completed', 'short_url',
		'funds', 'earnings',
		'avatar',
		'referral_completed', 'referred_by', 'referrals', 'commission',
		'start_completed'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	protected $casts = [
		'streamer' => 'boolean',
		'funds' => 'float',
		'earnings' => 'float',
		'newsletter_enabled' => 'boolean',
		'email_confirmed' => 'boolean'
	];

	/**
	 * List of permission IDs that the user has.
	 *
	 * @var array
	 */
	protected $permissions = [];

	/**
	 * List of role IDs that the user belongs to.
	 *
	 * @var array
	 */
	protected $roles = [];

	/**
	 * {@inheritdoc}
	 */
	public function hasPermissionTo($permissionId)
	{
		return in_array($permissionId, $this->permissions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addPermission($permissionId)
	{
		if (array_search($permissionId, $this->permissions) === false)
		{
			array_push($this->permissions, $permissionId);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function removePermission($permissionId)
	{
		if (($key = array_search($permissionId, $this->permissions)) !== false)
		{
			unset($this->permissions[$key]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPermissions(array $permissionIDs)
	{
		$this->permissions = $permissionIDs;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isPartOf($roleId)
	{
		return in_array($roleId, $this->roles);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addRole($roleId)
	{
		if (array_search($roleId, $this->roles) === false)
		{
			array_push($this->roles, $roleId);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRoles(array $roleIDs)
	{
		$this->roles = $roleIDs;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeRole($roleId)
	{
		if (($key = array_search($roleId, $this->roles)) !== false)
		{
			unset($this->roles[$key]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	public function pledges()
	{
		return $this->hasMany('App\Models\Pledge');
	}

	public function matches()
	{
		return $this->hasMany('App\Models\Match');
	}

}
