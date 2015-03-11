<?php namespace App\Contracts\Service;

interface Payments {

	/**
	 * Create a new customer in the payment portal.
	 *
	 * @param array $data
	 *
	 * @return string The payment platform's customer identifier
	 */
	public function createCustomer(array $data);

	/**
	 * Update an existing customer with new data.
	 *
	 * @param int $customerId
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function updateCustomer($customerId, array $data);

	/**
	 * Charge a customer the specified amount.
	 *
	 * @param int $customerId
	 * @param float $amount
	 *
	 * @return string The payment platform's charge identifier
	 */
	public function chargeCustomer($customerId, $amount);

}