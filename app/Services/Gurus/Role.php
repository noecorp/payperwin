<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Role as RoleGuruInterface;

class Role implements RoleGuruInterface {

	const ADMIN = 1;

	protected $ids = [
		self::ADMIN => 'Admin',
	];

	/**
	 * {@inheritdoc}
	 */
	public function admin()
	{
		return $this->role(self::ADMIN);
	}

	/**
	 * Get the array containing the Role ID and name.
	 *
	 * @return array|null
	 */
	protected function role($id)
	{
		if (!isset($this->ids[$id])) return null;

		return ['id' => $id, 'name' => $this->ids[$id]];
	}

}
