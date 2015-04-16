<?php namespace App\Models;

class Aggregation extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'aggregations';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'type', 'reason', 
		'day', 'week', 'month', 'year',
		'amount'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	protected $casts = [
		'amount' => 'float'
	];

}
