<?php namespace App\Commands;

use App\Commands\Command;
use Carbon\Carbon;
use App\Contracts\Service\Gurus\Aggregation as AggregationGuru;

abstract class AggregateData extends Command {

	protected function getDateData(Carbon $date, AggregationGuru $guru, $type)
	{
		$data = [
			'day' => 0,
			'week' => 0,
			'month' => 0,
			'year' => 0,
		];

		switch ($type) {
			case $guru->daily():
				$data['day'] = $date->day;
				$data['month'] = $date->month;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->weekly():
				$data['week'] = $date->weekOfYear;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->monthly():
				$data['month'] = $date->month;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->yearly():
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->total():
				break;
			default:
				$data = [];
				break;
		}

		return $data;
	}
	
}
