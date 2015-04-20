<?php namespace App\Services;

use App\Contracts\Service\Distribution as DistributionInterface;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Matches;
use App\Contracts\Repository\Pledges;
use App\Contracts\Repository\Transactions;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Contracts\Service\Acidifier as AcidifierInterface;
use App\Contracts\Service\Gurus\Transaction as TransactionGuru;
use App\Models\User;

class Distribution implements DistributionInterface {

	/**
	 * Users repository instance.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Matches repository instance.
	 *
	 * @var Matches
	 */
	protected $matches;

	/**
	 * Pledges repository instance.
	 *
	 * @var Pledges
	 */
	protected $pledges;

	/**
	 * Transactions repository instance.
	 *
	 * @var Transactions
	 */
	protected $transactions;

	/**
	 * Acidifier service instance.
	 *
	 * @var AcidifierInterface
	 */
	protected $acidifier;

	/**
	 * Transaction Guru service instance.
	 *
	 * @var TransactionGuru
	 */
	protected $transactionGuru;

	/**
	 * Create new Distribution service instance.
	 *
	 * @param Users $users
	 * @param Matches $matches
	 * @param Pledges $pledges
	 * @param AcidifierInterface $acidifier
	 * @param Transactions $transactions
	 */
	public function __construct(Users $users, Matches $matches, Pledges $pledges, Transactions $transactions, AcidifierInterface $acidifier, TransactionGuru $transactionGuru)
	{
		$this->users = $users;
		$this->matches = $matches;
		$this->pledges = $pledges;
		$this->transactions = $transactions;
		$this->acidifier = $acidifier;
		$this->transactionGuru = $transactionGuru;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pledgesFor($streamerId)
	{
		$pledges = $this->pledges->withOwner()->isRunning()->forStreamer($streamerId)->all();

		// First, stop all expired pledges
		$expired = $pledges->filter(function($pledge) {
			// This includes reaching their set end date, ...
			$dated = ($pledge->end_date !== null && Carbon::now()->gte($pledge->end_date));
			
			// ...reaching their max win limit, ...
			$won = ($pledge->win_limit !== null && $pledge->times_donated >= $pledge->win_limit);

			// ...and reaching their total pledged limit.
			$spent = ($pledge->spending_limit !== null && ($pledge->times_donated * $pledge->amount) >= $pledge->spending_limit);

			return ($dated || $won || $spent);
		});
		
		$this->stopExpired($expired);

		// Then, process the remaining ones
		$running = $pledges->diff($expired);

		$this->processRunning($running, $streamerId);
	}

	protected function stopExpired(Collection $expired)
	{
		// Do a mass update switching them all off at once
		$this->pledges->updateAll($expired->map(function($pledge) {
			return $pledge->id;
		})->toArray(), [
			'running' => 0
		]);
	}

	protected function processRunning(Collection $running, $streamerId)
	{
		
		// Potential for DB::connection()->disableQueryLog(); somewhere here

		$streamer = $this->users->find($streamerId);

		$unsettledMatches = $this->matches->forStreamer($streamerId)->isUnsettled()->all();

		// So here's where we need to be careful, so we'll use a query transaction...
		$this->acidifier->transaction(function() use ($unsettledMatches, $running, $streamer)
		{
			// Total funds to add to streamer
			$funds = 0;

			// Financial transactions to log in the database
			$transactions = [];

			// Pledges that have just expired
			$expired = [];

			foreach ($unsettledMatches as $match)
			{
				// Only care about won matches!
				if (!$match->win)
				{
					continue;
				}

				foreach ($running as $pledge)
				{
					if ($pledge->owner->funds < $pledge->amount) continue;

					// Increment total funds to be added to streamer account
					$funds += $pledge->amount;

					// Decrement the pledger's funds
					$this->users->update($pledge->owner, ['funds' => $pledge->owner->funds - $pledge->amount]);

					// Pledge has reached its max win limit
					$won = ($pledge->win_limit !== null && ($pledge->win_limit - 1) == $pledge->times_donated);

					// Pledge has reached its total pledged limit
					$spent = ($pledge->spending_limit !== null && ($pledge->spending_limit - ($pledge->times_donated * $pledge->amount)) == $pledge->amount);

					// Add this pledge to the expired list
					if ($won || $spent)
					{
						$expired[] = $pledge->id;
					}

					// Add a financial transaction for the pledger
					$transactions[] = [
						'transaction_type' => $this->transactionGuru->pledgeTaken(),
						'amount' => $pledge->amount,
						'user_id' => $pledge->owner->id,
						'source' => 0,
						'reference' => null,
						'pledge_id' => $pledge->id,
						'username' => $streamer->username
					];

					// Add a financial transaction for the streamer
					$transactions[] = [
						'transaction_type' => $this->transactionGuru->pledgePaid(),
						'amount' => $pledge->amount,
						'user_id' => $streamer->id,
						'source' => 0,
						'reference' => null,
						'pledge_id' => $pledge->id,
						'username' => $pledge->owner->username
					];
				}
			}

			// All unsettled matches can be considered settled now
			$this->matches->updateAll($unsettledMatches->map(function($match)
			{
				return $match->id;
			})->toArray(), ['settled' => 1]);

			// Increment the pledges' donation counters
			$this->pledges->incrementAll($running->map(function($pledge)
			{
				return $pledge->id;
			})->toArray(), 'times_donated');

			// Add the total funds gather from pledges to the streamer's earnings
			$this->users->update($streamer, ['earnings' => $streamer->earnings + $funds]);

			// Stop expired pledges
			$this->pledges->updateAll($expired, ['running' => 0]);

			// We're going to bulk insert the Transactions objects to cut down on total number of queries
			$this->transactions->createAll($transactions);
		});
	}

}
