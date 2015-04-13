<?php namespace AppTests\Functional\Controllers;

use App\Models\User;

class WelcomeTest extends \AppTests\TestCase {

	public function testIndexOkWhenGuest()
	{
		$response = $this->call('GET','/');

		$this->assertResponseOk();
		$this->assertResponseIsView($response);
	}

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
		$this->assertResponseIsView($response);
	}

	public function testStartRedirectsIfGuest()
	{
		$response = $this->call('GET','start');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('auth/login'));
	}

}
