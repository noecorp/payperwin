<?php namespace App\Services;


/**
 * Based on:
 * - https://github.com/asm-products/payments
 * - https://stripe.com/docs/stripe.js
 * - https://stripe.com/docs/stripe.js/switching
 */


use App\Contracts\Service\Payments as PaymentsInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\TooManyRedirectsException;

class Payments implements PaymentsInterface {

	protected $client;

	protected $baseUrl;

	protected $authToken;

	protected $productUUID;

	public function __construct(Client $client)
	{
		$this->client = $client;

		$this->baseUrl = env('PAYMENTS_URL');

		$this->authToken = env('PAYMENTS_AUTH_TOKEN');

		$this->productUUID = env('PAYMENTS_PRODUCT_UUID');
	}

	/**
	 * {@inheritdoc}
	 */
	public function createCustomer(array $data)
	{
		try
		{
			$response = $this->client->post($this->baseUrl . 'products/'. $this->productUUID . '/customers', [
				'headers' => $this->headers(),
				'body' => $data,
				'timeout' => $this->timeout()
			]);

			$object = $response->json();
			return $object['id'];
		}
		catch (ClientException $e)
		{
			echo $e->getRequest();
			if ($e->hasResponse()) {
		        echo $e->getResponse();
		    }
			// handle
		}
		catch (ServerException $e)
		{
			//
		}
		catch (TooManyRedirectsException $e)
		{
			//
		}
		catch (ParseException $e)
		{
			//
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateCustomer($customerId, array $data)
	{
		try
		{
			$response = $this->client->put($this->baseUrl . 'products/'. $this->productUUID . '/customers/' . $customerId, [
				'headers' => $this->headers(),
				'body' => $data,
				'timeout' => $this->timeout()
			]);

			return true;
		}
		catch (ClientException $e)
		{
			echo $e->getRequest();
			if ($e->hasResponse()) {
		        echo $e->getResponse();
		    }
			// handle
		}
		catch (ServerException $e)
		{
			//
		}
		catch (TooManyRedirectsException $e)
		{
			//
		}
		catch (ParseException $e)
		{
			//
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function chargeCustomer($customerId, $amount)
	{
		try
		{
			$response = $this->client->post($this->baseUrl . 'products/'. $this->productUUID . '/charges', [
				'headers' => $this->headers(),
				'body' => [
					'customer' => $customerId,
					'amount' => $amount * 100, // cents
					'currency' => 'usd'
				'timeout' => $this->timeout()
			]);

			$object = $response->json();
			return $object['id'];
		}
		catch (ClientException $e)
		{
			echo $e->getRequest();
			if ($e->hasResponse()) {
		        echo $e->getResponse();
		    }
			// handle
		}
		catch (ServerException $e)
		{
			//
		}
		catch (TooManyRedirectsException $e)
		{
			//
		}
		catch (ParseException $e)
		{
			//
		}
	}

	protected function headers()
	{
		return [
			'content-type' => 'application/json'
			'authorization' => $this->authToken
		];
	}

	protected function timeout()
	{
		return 120; //seconds
	}

}