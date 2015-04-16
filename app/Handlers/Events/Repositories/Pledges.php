<?php namespace App\Handlers\Events\Repositories;

use App\Events\Repositories\PledgeWasCreated;
use App\Events\Repositories\PledgeWasUpdated;
use App\Events\Repositories\PledgesWereCreated;
use App\Events\Repositories\PledgesWereUpdated;

use App\Contracts\Events\Model;
use App\Contracts\Events\Models;
use App\Contracts\Repository\Users as UsersRepository;
use App\Contracts\Service\Acidifier as AcidifierInterface;

use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Bus\QueueingDispatcher as Dispatcher;
use App\Commands\AggregateDataFromPledge;

class Pledges {

	/**
	 * Command dispatcher implementation.
	 *
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Users repository implementation.
	 *
	 * @var UsersRepository
	 */
	protected $users;

	/**
	 * Acidifier implementation.
	 *
	 * @var AcidifierInterface
	 */
	protected $acid;

	/**
	 * Create the event handler.
	 *
	 * @param Dispatcher $dispatcher
	 * @param UsersRepository $users
	 *
	 * @return void
	 */
	public function __construct(Dispatcher $dispatcher, UsersRepository $users, AcidifierInterface $acid)
	{
		$this->dispatcher = $dispatcher;
		$this->users = $users;
		$this->acid = $acid;
	}

	/**
	 * Handle single pledge creation events.
	 *
	 * @param Model $event
	 */
	public function onPledgeWasCreated(Model $event)
	{
		$pledge = $event->model();

		$streamer = $this->users->find($pledge->streamer_id);

		$this->dispatcher->dispatchToQueue(new AggregateDataFromPledge($pledge->id));

		if ($streamer->referred_by && !$streamer->referral_completed)
		{
			$referrer = $this->users->find($streamer->referred_by);

			$this->acid->transaction(function() use ($streamer, $referrer)
			{
				$this->users->update($streamer, ['referral_completed' => true]);
				$this->users->increment($referrer, 'referrals');
			});
		}
	}

	/**
	 * Handle single pledge update events.
	 *
	 * @param Model $event
	 */
	public function onPledgeWasUpdated(Model $event)
	{

	}

	/**
	 * Handle mass pledge creation events.
	 *
	 * @param Models $event
	 */
	public function onPledgesWereCreated(Models $event)
	{

	}

	/**
	 * Handle single pledge update events.
	 *
	 * @param Models $event
	 */
	public function onPledgesWereUpdated(Models $event)
	{

	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Events  $events
	 * @return array
	 */
	public function subscribe(Events $events)
	{
		$events->listen(PledgeWasCreated::class, 'App\Handlers\Events\Repositories\Pledges@onPledgeWasCreated');
		$events->listen(PledgesWereCreated::class, 'App\Handlers\Events\Repositories\Pledges@onPledgesWereCreated');
		$events->listen(PledgeWasUpdated::class, 'App\Handlers\Events\Repositories\Pledges@onUPledgeWasUpdated');
		$events->listen(PledgesWereUpdated::class, 'App\Handlers\Events\Repositories\Pledges@onPledgesWereUpdated');
	}

}
