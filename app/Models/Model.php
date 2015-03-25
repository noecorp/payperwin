<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent {

	/**
	 * The attributes that should be set to null if empty.
	 *
	 * @var array
	 */
	protected $nullable = [];

	/**
	 * {@inheritdoc}
	 */
	public function save(array $options = array())
	{
		foreach ($this->nullable as $key)
		{
			if (isset($this->attributes[$key]) && $this->attributes[$key] === '')
			{
				$this->attributes[$key] = null;
			}
		}
		
		return parent::save($options);
	}

}
