<?php namespace App\Contracts\Service;

use Closure;

interface Acidifier {

	/**
	 * Perform a database transaction.
	 *
	 * @param Closure $closure
	 *
	 * @return mixed
	 */
	public function transaction(Closure $closure);

}
