<?php namespace App\Contracts\Models;

interface Permissible {
	
	/**
	 * Whether or not the user has the specified permission.
	 *
	 * @param int $permissionId
	 *
	 * @return boolean
	 */
	public function hasPermissionTo($permissionId);

	/**
	 * Add a permission id to the user's local list of permissions.
	 *
	 * @param int $permissiondId
	 *
	 * @return void
	 */
	public function addPermission($permissionId);

	/**
	 * Remove a permission id from the user's local list of permissions.
	 *
	 * @param int $permissiondId
	 *
	 * @return void
	 */
	public function removePermission($permissionId);

	/**
	 * Set the local list of the user's permission IDs.
	 *
	 * @param array $permissionIDs
	 *
	 * @return void
	 */
	public function setPermissions(array $permissionIDs);

	/**
	 * Get the list of local permission IDs for the user.
	 *
	 * @return array
	 */
	public function getPermissions();

	/**
	 * Whether or not the user has the specified role.
	 *
	 * @param int $roleId
	 *
	 * @return boolean
	 */
	public function isPartOf($roleId);

	/**
	 * Add a role id to the user's local list of roles.
	 *
	 * @param int $roleId
	 *
	 * @return void
	 */
	public function addRole($roleId);

	/**
	 * Set the local list of the user's role IDs.
	 *
	 * @param array $roleIDs
	 *
	 * @return void
	 */
	public function setRoles(array $roleIDs);

	/**
	 * Remove a role id from the user's local list of roles.
	 *
	 * @param int $roleId
	 *
	 * @return void
	 */
	public function removeRole($roleId);

	/**
	 * Get the list of local role IDs for the user.
	 *
	 * @return array
	 */
	public function getRoles();

}
