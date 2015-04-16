<?php namespace AppTests\Functional\Controllers;

use App\Models\User;

/**
 * @coversDefaultClass \App\Http\Controllers\Welcome
 */
class WelcomeTest extends \AppTests\TestCase {

	/**
     * {@inheritdoc}
     */
    protected $migrate = true;

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::index
	 */
	public function testIndexOkWhenGuest()
	{
		$response = $this->call('GET','/');

		$this->assertResponseOk();
		$this->assertResponseIsView();
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::index
	 */
	public function testIndexRedirectsIfLoggedIn()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);

		$this->be($user);

		$response = $this->call('GET','/');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('start'));
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::start
	 */
	public function testStartOkWhenLoggedIn()
	{
		$user = User::create(
		[
			'email' => 'foo@bar.com',
			'username' => 'foo',
		]);
		
		$this->be($user);
		
		$response = $this->call('GET','start');

		$this->assertResponseOk();
		$this->assertResponseIsView();
	}

	/**
	 * @small
	 *
	 * @group controllers
	 *
	 * @covers ::__construct
	 * @covers ::start
	 */
	public function testStartRedirectsIfGuest()
	{
		$response = $this->call('GET','start');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

}
