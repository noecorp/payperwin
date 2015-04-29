<?php namespace AppTests\Unit\Models;

use App\Models\User;

/**
 * @coversDefaultClass \App\Models\User
 */
class UserTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = false;

	/**
	 * @small
	 *
	 * @group models
	 *
	 * @covers ::__construct
	 * @covers ::setPermissions
	 * @covers ::hasPermissionTo
	 * @covers ::addPermission
	 * @covers ::removePermission
	 * @covers ::getPermissions
	 */
	public function test_permissions()
	{
		$user = new User();

		$this->assertFalse($user->hasPermissionTo(1));

		$user->addPermission(1);
		$user->addPermission(1);

		$this->assertTrue($user->hasPermissionTo(1));

		$this->assertEquals([1], $user->getPermissions());

		$user->setPermissions([2,3]);

		$this->assertTrue($user->hasPermissionTo(2));
		$this->assertTrue($user->hasPermissionTo(3));
		$this->assertFalse($user->hasPermissionTo(1));

		$this->assertEquals([2,3], $user->getPermissions());

		$user->removePermission(3);
		$user->removePermission(3);

		$this->assertTrue($user->hasPermissionTo(2));
		$this->assertFalse($user->hasPermissionTo(3));

		$this->assertEquals([2], $user->getPermissions());
	}

	/**
	 * @small
	 *
	 * @group models
	 *
	 * @covers ::__construct
	 * @covers ::setRoles
	 * @covers ::isPartOf
	 * @covers ::addRole
	 * @covers ::removeRole
	 * @covers ::getRoles
	 */
	public function test_roles()
	{
		$user = new User();

		$this->assertFalse($user->isPartOf(1));

		$user->addRole(1);
		$user->addRole(1);

		$this->assertTrue($user->isPartOf(1));

		$this->assertEquals([1], $user->getRoles());

		$user->setRoles([2,3]);

		$this->assertTrue($user->isPartOf(2));
		$this->assertTrue($user->isPartOf(3));
		$this->assertFalse($user->isPartOf(1));

		$this->assertEquals([2,3], $user->getRoles());

		$user->removeRole(3);
		$user->removeRole(3);

		$this->assertTrue($user->isPartOf(2));
		$this->assertFalse($user->isPartOf(3));

		$this->assertEquals([2], $user->getRoles());
	}

}
