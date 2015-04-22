<?php namespace App\Commands;

use App\Commands\Command;
use Carbon\Carbon;
use App\Contracts\Service\Gurus\Aggregation as AggregationGuru;

abstract class AggregateData extends Command {

	protected function getDateData(Carbon $date, AggregationGuru $guru, $type)
	{
		$data = [];
		
		switch ($type) {
			case $guru->daily():
				$data['day'] = $date->day;
				$data['week'] = 0;
				$data['month'] = $date->month;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->weekly():
				$data['day'] = 0;
				$data['week'] = $date->weekOfYear;
				$data['month'] = 0;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->monthly():
				$data['day'] = 0;
				$data['week'] = 0;
				$data['month'] = $date->month;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->yearly():
				$data['day'] = 0;
				$data['week'] = 0;
				$data['month'] = 0;
				$data['year'] = (int)$date->format('y');
				break;
			case $guru->total():
				$data['day'] = 0;
				$data['week'] = 0;
				$data['month'] = 0;
				$data['year'] = 0;
				break;
			default:
				break;
		}

		return $data;
	}
	
}
