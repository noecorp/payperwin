<?php namespace App\Commands;

use App\Commands\AggregateData;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Aggregations;
use App\Contracts\Service\Gurus\Aggregation as Guru;
use App\Contracts\Service\Acidifier;
use Illuminate\Contracts\Cache\Repository as Cache;

use App\Models\Aggregation;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AggregateDataFromPledge extends AggregateData implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * {@inheritdoc}
	 */
	protected $unique = true;

	/**
	 * Pledge identifier.
	 *
	 * @var int
	 */
	protected $pledgeId;

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
	 * The Pledge object.
	 *
	 * @var Pledge
	 */
	protected $pledge;

	/**
	 * Cache repository implementation.
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * Pledges repositorory implementation.
	 *
	 * @var Pledges
	 */
	protected $pledges;

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
	public function __construct($pledgeId)
	{
		parent::__construct($pledgeId);

		$this->pledgeId = $pledgeId;
	}

	/**
	 * Execute the command.
	 *
	 * @param Pledges $pledges
	 * @param Aggregations $aggregations
	 * @param Guru $guru
	 * @param Acidifier $acid
	 * @param Cache $cache
	 *
	 * @return void
	 */
	public function handle(Pledges $pledges, Aggregations $aggregations, Acidifier $acid, Guru $guru, Cache $cache)
	{
		$this->aggregations = $aggregations;
		$this->guru = $guru;
		$this->cache = $cache;
		$this->pledges = $pledges;
		$this->acid = $acid;

		$this->start();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function work()
	{
		$this->pledge = $this->pledges->find($this->pledgeId);

		if (!$this->pledge)
		{
			$this->delete();
			
			return;
		}

		$fromUser = $this->aggregations->forUser($this->pledge->user_id)->forReason($this->guru->pledgeFromUser())->all();

		$forStreamer = $this->aggregations->forUser($this->pledge->streamer_id)->forReason($this->guru->pledgeToStreamer())->all();

		$this->acid->transaction(function() use ($fromUser, $forStreamer)
		{
			// First the user's part...
			$this->updateOrCreate($fromUser, $this->guru->pledgeFromUser());

			// Then the streamer part...
			$this->updateOrCreate($forStreamer, $this->guru->pledgeToStreamer());

			// Since we're doing a bunch of saving, failing to delete a job may result in wrong numbers,
			// so we put the delete in the transaction as well.
			$this->delete();
		});
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
			$this->aggregations->incrementAll($ids, 'amount');
		}

		$remaining = (empty($present)) ? $this->guru->types() : array_diff($this->guru->types(),$present);

		$date = $this->pledge->created_at;

		foreach ($remaining as $type)
		{
			$data = $this->getDateData($date, $this->guru, $type);

			$data['reason'] = $reason;
			$data['amount'] = 1;
			$data['type'] = $type;
			$data['user_id'] = ($reason == $this->guru->pledgeFromUser()) ? $this->pledge->user_id : $this->pledge->streamer_id;

			$this->aggregations->create($data);
		}
	}

}
