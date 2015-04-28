<?php namespace App\Commands;

use App\Commands\AggregateData;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;
use App\Contracts\Repository\Aggregations;
use App\Contracts\Service\Gurus\Aggregation as Guru;
use App\Contracts\Service\Acidifier;
use Illuminate\Contracts\Cache\Repository as Cache;

use App\Models\Aggregation;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AggregateDataFromUserUpdate extends AggregateData implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * {@inheritdoc}
	 */
	protected $unique = true;

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
	 * Cache repository implementation.
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * Users repositorory implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Acidifier implementation.
	 *
	 * @var Acidifier
	 */
	protected $acid;

	/**
	 * Create a new command instance.
	 *
	 * @param int $pledgeId
	 *
	 * @return void
	 */
	public function __construct($userId, array $changed, array $current)
	{
		parent::__construct($userId, $changed, $current);

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
	 * @param Cache $cache
	 *
	 * @return void
	 */
	public function handle(Users $users, Aggregations $aggregations, Acidifier $acid, Guru $guru, Cache $cache)
	{
		$this->aggregations = $aggregations;
		$this->guru = $guru;
		$this->cache = $cache;
		$this->users = $users;
		$this->acid = $acid;

		$this->start();
	}


	/**
	 * {@inheritdoc}
	 */
	protected function work()
	{
		$user = $this->users->find($this->userId);
		
		if (!$user)
		{
			$this->delete();

			return;
		}

		foreach ($this->current as $key => $val)
		{
			if ($user->$key != $val)
			{
				$this->delete();

				return;

				break;
			}
		}

		$fromUser = (isset($this->changed['funds']) && $this->changed['funds'] > $this->current['funds']) ? $this->getUserAggregations($this->current['updated_at']) : null;

		$forStreamer = (isset($this->changed['earnings'])) ? $this->getStreamerAggregations($this->current['updated_at']) : null;

		$this->acid->transaction(function() use ($fromUser, $forStreamer)
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
			$data = $this->getDateData($date, $this->guru, $type);

			$data['reason'] = $reason;
			$data['amount'] = ($reason == $this->guru->paidByUser()) ? $this->changed['funds'] - $this->current['funds'] : $this->current['earnings'] - $this->changed['earnings'];
			$data['type'] = $type;
			$data['user_id'] = $this->userId;

			$this->aggregations->create($data);
		}
	}

}
