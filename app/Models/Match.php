<?php namespace App\Models;

class Match extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'matches';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'server_match_id', 'win', 'champion', 'kills', 'assists', 'deaths', 'settled', 'match_date'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	protected $casts = [
		'settled' => 'boolean',
		'win' => 'boolean'
	];

	protected $dates = [
		'match_date'
	];

}
