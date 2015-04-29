<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Permission as PermissionGuruInterface;

class Permission implements PermissionGuruInterface {

	protected $ids = [
		
	];

	/**
	 * Get the array containing the Role ID and name.
	 *
	 * @return array|null
	 */
	protected function permission($id)
	{
		if (!isset($this->ids[$id])) return null;

		return ['id' => $id, 'name' => $this->ids[$id]];
	}

}
