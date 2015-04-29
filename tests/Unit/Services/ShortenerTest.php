<?php namespace AppTests\Unit\Services;

use Mockery as m;
use Illuminate\Support\Facades\DB;
use App\Services\Shortener;
use GuzzleHttp\Client;

/**
 * @coversDefaultClass \App\Services\Shortener
 */
class ShortenerTest extends \AppTests\TestCase {

	/**
	 * {@inheritdoc}
	 */
	protected $migrate = false;

	private function getShortener()
	{
		return new Shortener($this->app->make(Client::class));
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::url
	 */
	public function test_url_ok()
	{
		$this->mockGuzzle([
			201,
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
		], [
			'{"url":"baz"}'
		]);

		$shortener = $this->getShortener();

		$url = $shortener->url('foo', 'bar');

		$this->assertEquals('baz', $url);
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::url
	 */
	public function test_url_errors()
	{
		$this->mockGuzzle([
			400,
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
		], [
			'{"error":["foo":"bar"]}'
		]);

		$shortener = $this->getShortener();

		$url = $shortener->url('foo', 'bar');

		$this->assertNull($url);
	}

	/**
	 * @small
	 *
	 * @group services
	 *
	 * @covers ::__construct
	 * @covers ::url
	 */
	public function test_url_bad_json()
	{
		$this->mockGuzzle([
			201,
		], [
			['Content-Type'=>'application/json; charset=UTF-8'],
		], [
			'foo'
		]);

		$shortener = $this->getShortener();

		$url = $shortener->url('foo', 'bar');

		$this->assertNull($url);
	}

}
