<?php namespace AppTests\Functional\Controllers;

class WelcomeTest extends \AppTests\TestCase {

	public function testIndexOkWhenGuest()
	{
		$response = $this->call('GET','/');

		$this->assertResponseOk();
		$this->assertResponseIsView($response);
	}

	public function testIndexRedirectsIfLoggedIn()
	{
		$this->become(1);

		$response = $this->call('GET','/');

		$this->assertResponseStatus(302);
		$this->assertResponseHeaderIs('Location',url('start'));
	}

	public function testStartOkWhenLoggedIn()
	{
		$this->become(1);
		
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
