<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;
use App\Contracts\Repository\Aggregations;
use App\Contracts\Service\Gurus\Aggregation as Guru;
use App\Contracts\Service\Acidifier;

use App\Models\Aggregation;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AggregateDataFromUserUpdate extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * User identifier.
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * Aggregations repository implementation.
	 *
	 * @var Aggregations
	 */
	protected $aggregations;

	/**
	 * Aggregation Guru implementation.
	 *
	 * @var Guru
	 */
	protected $guru;

	/**
	 * List of changed attributes.
	 *
	 * @var array
	 */
	protected $changed;

	/**
	 * Previous user attributes.
	 *
	 * The reason that we don't get this from the user object is because
	 * another update might have happened by the time this command has been
	 * triggered in the queue.
	 *
	 * @var array
	 */
	protected $current;

	/**
	 * Create a new command instance.
	 *
	 * @param int $pledgeId
	 *
	 * @return void
	 */
	public function __construct($userId, array $changed, array $current)
	{
		$this->userId = $userId;
		$this->changed = $changed;
		$this->current = $current;
	}

	/**
	 * Execute the command.
	 *
	 * @param Users $users
	 * @param Aggregations $aggregations
	 * @param Guru $guru
	 * @param Acidifier $acid
	 *
	 * @return void
	 */
	public function handle(Users $users, Aggregations $aggregations, Acidifier $acid, Guru $guru)
	{
		$this->aggregations = $aggregations;
		$this->guru = $guru;

		if (!$users->find($this->userId))
		{
			$this->delete();

			return;
		}

		$fromUser = (isset($this->changed['funds']) && $this->changed['funds'] > $this->current['funds']) ? $this->getUserAggregations($this->current['updated_at']) : null;

		$forStreamer = (isset($this->changed['earnings'])) ? $this->getStreamerAggregations($this->current['updated_at']) : null;

		$acid->transaction(function() use ($fromUser, $forStreamer)
		{
			// First the user's part...
			if ($fromUser !== null)
			{
				$this->updateOrCreate($fromUser, $this->guru->paidByUser());
			}

			// Then the streamer part...
			if ($forStreamer !== null)
			{
				$this->updateOrCreate($forStreamer, $this->guru->paidToStreamer());
			}

			// Since we're doing a bunch of saving, failing to delete a job may result in wrong numbers,
			// so we put the delete in the transaction as well.
			$this->delete();
		});
	}

	private function getUserAggregations($date)
	{
		return $this->aggregations->forUser($this->userId)
			->forReason($this->guru->paidByUser())
			->forDate(new Carbon($date))
			->all();
	}

	private function getStreamerAggregations($date)
	{
		return $this->aggregations->forUser($this->userId)
			->forReason($this->guru->paidToStreamer())
			->forDate(new Carbon($date))
			->all();
	}

	private function updateOrCreate(Collection $collection, $reason)
	{
		$present = [];
		$ids = [];

		foreach ($collection as $aggregation)
		{
			$present[] = $aggregation->type;
			$ids[] = $aggregation->id;
		}

		if (!empty($ids))
		{
			if ($reason == $this->guru->paidByUser())
			{
				$this->aggregations->incrementAll($ids, 'amount', $this->changed['funds'] - $this->current['funds']);
			}
			else
			{
				$this->aggregations->incrementAll($ids, 'amount', $this->current['earnings'] - $this->changed['earnings']);	
			}
		}

		$remaining = (empty($present)) ? $this->guru->types() : array_diff($this->guru->types(),$present);

		$date = new Carbon($this->current['updated_at']);

		foreach ($remaining as $type)
		{
			$data = [
				'reason' => $reason,
				'amount' => ($reason == $this->guru->paidByUser()) ? $this->changed['funds'] - $this->current['funds'] : $this->current['earnings'] - $this->changed['earnings'],
				'type' => $type,
				'user_id' => $this->userId
			];

			switch ($type) {
				case $this->guru->daily():
					$data['day'] = $date->day;
					$data['week'] = 0;
					$data['month'] = $date->month;
					$data['year'] = (int)$date->format('y');
					break;
				case $this->guru->weekly():
					$data['day'] = 0;
					$data['week'] = $date->weekOfYear;
					$data['month'] = 0;
					$data['year'] = (int)$date->format('y');
					break;
				case $this->guru->monthly():
					$data['day'] = 0;
					$data['week'] = 0;
					$data['month'] = $date->month;
					$data['year'] = (int)$date->format('y');
					break;
				case $this->guru->yearly():
					$data['day'] = 0;
					$data['week'] = 0;
					$data['month'] = 0;
					$data['year'] = (int)$date->format('y');
					break;
				case $this->guru->total():
					$data['day'] = 0;
					$data['week'] = 0;
					$data['month'] = 0;
					$data['year'] = 0;
					break;
				default:
					break;
			}

			$this->aggregations->create($data);
		}
	}

}
