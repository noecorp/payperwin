<?php namespace AppTests\Unit;

class HelpersTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = false;

	/**
	 * @small
	 *
	 * @group helpers
	 *
	 */
	public function test_app_url()
	{
		// Here url() looks at env, so these should be equal
		$this->assertEquals(url(), app_url());

		// Same here
		$this->assertEquals(url('foo', ['bar','baz']), app_url('foo', ['bar','baz']));

		// Now we change the env variable
		putenv("APP_URL=https://faz.baz");

		$this->assertEquals('https://faz.baz/foo/bar/baz', app_url('foo', ['bar','baz']));
		$this->assertNotEquals(url('foo', ['bar','baz']), app_url('foo', ['bar','baz']));

		// Now we test with trailing slash
		putenv("APP_URL=https://faz.baz/");

		$this->assertEquals('https://faz.baz/foo/bar/baz', app_url('foo', ['bar','baz']));
	}

}
