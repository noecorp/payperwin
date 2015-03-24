<?php namespace App\Models;

class Pledge extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'pledges';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['amount','type','message','win_limit','sum_limit','user_id','streamer_id','end_date'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	protected $dates = ['end_date'];

	protected $casts = [
		'running' => 'boolean'
	];

	/**
	 * The attributes that should be set to null if empty.
	 *
	 * @var array
	 */
	protected $nullable = ['end_date'];

	public function owner()
	{
		return $this->belongsTo('App\Models\User','user_id');
	}

	public function streamer()
	{
		return $this->belongsTo('App\Models\User','streamer_id');
	}

}
