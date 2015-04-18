<?php namespace AppTests;

use App\Models\User;

use PHPUnit_Framework_Assert as PHPUnit;
use Mockery as m;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class TestCase extends \Illuminate\Foundation\Testing\TestCase {

	/**
	 * Whether or not to run table database migrations to set up tables.
	 *
	 * This shouldn't be needed in unit tests, but necessary in other test types.
	 *
	 * @var boolean
	 */
	protected $migrate = true;

	protected function getGuzzleMock($status = 200, $headers = [], $content = '', $howMany = 1)
	{
		$client = new GuzzleClient();

		$responses = [];

		if (is_array($status))
		{
			for ($i = 0; $i < count($status); $i++)
			{
				$responses[] = new Response($status[$i], $headers[$i], Stream::factory($content[$i]));
			}
		}
		else
		{
			for ($i = 1; $i <= $howMany; $i++)
			{
				$responses[] = new Response($status, $headers, Stream::factory($content));
			}
		}

		$mock = new Mock($responses);

		$client->getEmitter()->attach($mock);

        return $client;
	}

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
		return PHPUnit::assertEquals($value, $this->response->headers->get($key));
	}

	public function assertResponseIsJson()
	{
		return PHPUnit::assertNotNull(json_decode($this->response->getContent()));
	}

	public function assertResponseIsView()
	{
		return PHPUnit::assertTrue(is_object($this->response) && isset($this->response->original) && $this->response->original instanceof \Illuminate\View\View);
	}

	public function viewData($key = null)
	{
		if (is_object($this->response) && isset($this->response->original) && $this->response->original instanceof \Illuminate\View\View)
		{
			return ($key) ? $this->response->original->getData()[$key] : $this->response->original->getData();
		}
		else
		{
			return PHPUnit::assertTrue(false, 'The response was not a view.');
		}
	}

	public function responseJson($array = false)
	{
		return json_decode($this->response->getContent(),$array);
	}

	public function setUp()
	{
		parent::setUp();

		if ($this->migrate)
		{
			$this->artisan('migrate');
		}
	}

	public function tearDown()
	{
		if ($this->migrate)
		{
			$this->artisan('migrate:rollback');
		}
		
		$this->artisan('cache:clear');
		$this->flushSession();

		$this->artisan('clear:apc');

		parent::tearDown();
	}

	protected function clearLog()
	{
		exec('echo "" > '.storage_path().'/logs/laravel.log');
	}

	protected function getMockOf($className)
	{
		return m::mock($className);
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
