<?php namespace AppTests\Functional\Repositories;

use Mockery as m;
use Illuminate\Support\Facades\DB;
use App\Services\Gurus\Role;
use App\Repositories\Users;
use Illuminate\Contracts\Container\Container;

/**
 * @coversDefaultClass \App\Repositories\Users
 */
class UsersTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = true;

	private function getRepo()
	{
		return new Users($this->app->make(Container::class));
	}

	/**
	 * @small
	 *
	 * @group repositories
	 *
	 * @covers ::__construct
	 * @covers ::find
	 * @covers ::query
	 *
	 * @uses \App\Services\Guru\Role
	 */
	public function test_find()
	{
		$user = $this->fixture('users', [
			'email' => 'foo',
			'username' => 'bar'
		]);

		$user2 = $this->fixture('users', [
			'email' => 'foo2',
			'username' => 'bar2'
		]);

		$user3 = $this->fixture('users', [
			'email' => 'foo3',
			'username' => 'bar3'
		]);

		$user4 = $this->fixture('users', [
			'email' => 'foo4',
			'username' => 'bar4'
		]);

		$roleGuru = new Role();
		$admin = $roleGuru->admin();

		$this->fixture('role_user', [
			'role_id' => $admin['id'],
			'user_id' => $user->id
		]);

		$this->fixture('permission_user', [
			'permission_id' => 777,
			'user_id' => $user2->id
		]);

		$this->fixture('role_user', [
			'role_id' => $admin['id'],
			'user_id' => $user4->id
		]);

		$this->fixture('permission_user', [
			'permission_id' => 999,
			'user_id' => $user4->id
		]);

		$users = $this->getRepo();

		$user = $users->find($user->id);
		$user2 = $users->find($user2->id);
		$user3 = $users->find($user3->id);
		$user4 = $users->find($user4->id);

		$this->assertNotNull($user);
		$this->assertNotNull($user2);
		$this->assertNotNull($user3);
		$this->assertNotNull($user4);

		$this->assertTrue($user->isPartOf($admin['id']));
		$this->assertEmpty($user->getPermissions());

		$this->assertTrue($user2->hasPermissionTo(777));
		$this->assertEmpty($user2->getRoles());

		$this->assertEmpty($user3->getPermissions());
		$this->assertEmpty($user3->getRoles());

		$this->assertTrue($user4->isPartOf($admin['id']));
		$this->assertTrue($user4->hasPermissionTo(999));
	}

}
