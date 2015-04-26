<?php namespace App\Services;

use App\Contracts\Service\Shortener as ShortenerInterface;
use GuzzleHttp\Client;

class Shortener implements ShortenerInterface {

	/**
	 * Guzzle client implementation.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Api endpoint to call.
	 *
	 * @var string
	 */
	protected $apiUrl;

	/**
	 * Create a new Shortener instance.
	 *
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;

		$this->apiUrl = config('services.shortener.url');
	}

	/**
	 * {@inheritdoc}
	 */
	public function url($url, $slug)
	{
		try
		{
			$response = $this->client->post($this->apiUrl, [
				'body' => [
					'key' => config('services.shortener.key'),
					'url' => $url,
					'slug' => $slug
				],
				'connect_timeout' => 5,
				'exceptions' => true
			]);

			$object = $response->json();

			if (isset($object['url']))
			{
				return $object['url'];
			}
			else
			{
				// log $object['error'];
			}
		}
		catch (\Exception $e)
		{
			// log $e
		}

		return null;

	}

}
