<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

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
		'name', 'username', 'email',
		'twitch_id', 'facebook_id', 'twitch_username', 'live',
		'password',
		'streamer', 'summoner_id', 'summoner_name', 'region', 'streamer_completed',
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
		'earnings' => 'float'
	];

	public function pledges()
	{
		return $this->hasMany('App\Models\Pledge');
	}

	public function matches()
	{
		return $this->hasMany('App\Models\Match');
	}

}
