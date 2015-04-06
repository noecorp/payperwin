<?php namespace AppTests;

use App\Models\User;
use PHPUnit_Framework_Assert as PHPUnit;

class TestCase extends \Illuminate\Foundation\Testing\TestCase {

	public function become($id)
	{
		$user = User::find($id);
		
		$this->be($user);

		return $user;
	}

	/**
	 * Assert that the client response contains the given text.
	 *
	 * @param  string  $text
	 * @return void
	 */
	public function assertResponseHasContent($text)
	{
		return PHPUnit::assertNotFalse(strpos($this->response->getContent(), $text));
	}

	public function assertResponseHeaderIs($key,$value)
	{
		return PHPUnit::assertEquals($this->response->headers->get($key),$value);
	}

	public function assertResponseIsJson()
	{
		return PHPUnit::assertNotNull(json_decode($this->response->getContent()));
	}

	public function assertResponseIsView($response)
	{
		return PHPUnit::assertTrue(is_object($response) && isset($response->original) && $response->original instanceof \Illuminate\View\View);
	}

	public function responseJson()
	{
		return json_decode($this->response->getContent());
	}

	public function setUp()
	{
		parent::setUp();

		$this->artisan('migrate');
		$this->seed();
	}

	public function tearDown()
	{
		$this->artisan('migrate:rollback');
		$this->artisan('cache:clear');
		$this->flushSession();

		parent::tearDown();
	}

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$app = require __DIR__.'/../bootstrap/app.php';

		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

		return $app;
	}

}
