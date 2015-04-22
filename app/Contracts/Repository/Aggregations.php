<?php namespace App\Contracts\Repository;

use \DateTime;

interface Aggregations extends RepositoryContract {

	public function isDaily(DateTime $date = null);

	public function isWeekly(DateTime $date);

	public function isMonthly(DateTime $date);

	public function isYearly(DateTime $date);

	public function isTotal();

	public function forReason($reason);

	public function forUser($id);

	public function forDate(DateTime $date);
	
}
