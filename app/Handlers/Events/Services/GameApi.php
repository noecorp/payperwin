<?php namespace App\Handlers\Events\Services;

use App\Contracts\Events\GameApi as GameApiEvent;
use App\Events\Services\GameApi\AccessWasUnauthorized;
use App\Events\Services\GameApi\RateLimitWasExceeded;
use App\Events\Services\GameApi\RequestWasInvalid;
use App\Events\Services\GameApi\ServerHadAnError;
use App\Events\Services\GameApi\ServiceWasUnavailable;
use App\Events\Services\GameApi\UnknownErrorOccurred;

use Illuminate\Events\Dispatcher as Events;
use Illuminate\Contracts\Bus\QueueingDispatcher as Dispatcher;

use App\Commands\NotifyAboutGameApiIssue;

class GameApi {

	protected $dispatcher;

	/**
	 * Create the event handler.
	 *
	 * @param Dispatcher $dispatcher
	 *
	 * @return void
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Listen to game api returning unauthorized response.
	 *
	 * @param GameApiEvent $event
	 */
	public function onAccessWasUnauthorized(GameApiEvent $event)
	{
		$this->dispatcher->dispatchToQueue(new NotifyAboutGameApiIssue('unauthorized',$event->game(),$event->url(),$event->info()));
	}

	/**
	 * Listen to game api returning rate limit exceeded response.
	 *
	 * @param GameApiEvent $event
	 */
	public function onRateLimitWasExceeded(GameApiEvent $event)
	{
		$this->dispatcher->dispatchToQueue(new NotifyAboutGameApiIssue('rate-limit',$event->game(),$event->url(),$event->info()));
	}

	/**
	 * Listen to game api returning service unavailable response.
	 *
	 * @param GameApiEvent $event
	 */
	public function onServiceWasUnavailable(GameApiEvent $event)
	{
		$this->dispatcher->dispatchToQueue(new NotifyAboutGameApiIssue('service-unavailable',$event->game(),$event->url(),$event->info()));
	}

	/**
	 * Listen to game api returning bad request response.
	 *
	 * @param GameApiEvent $event
	 */
	public function onRequestWasInvalid(GameApiEvent $event)
	{
		$this->dispatcher->dispatchToQueue(new NotifyAboutGameApiIssue('bad-request',$event->game(),$event->url(),$event->info()));
	}

	/**
	 * Listen to game api returning server error response.
	 *
	 * @param GameApiEvent $event
	 */
	public function onServerHadAnError(GameApiEvent $event)
	{
		$this->dispatcher->dispatchToQueue(new NotifyAboutGameApiIssue('server-error',$event->game(),$event->url(),$event->info()));
	}

	/**
	 * Listen to unknown errors from game api.
	 *
	 * @param GameApiEvent $event
	 */
	public function onUnknownErrorOccurred(GameApiEvent $event)
	{
		$this->dispatcher->dispatchToQueue(new NotifyAboutGameApiIssue('unknown-error',$event->game(),$event->url(),$event->info()));
	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Events  $events
	 * @return array
	 */
	public function subscribe(Events $events)
	{
		$events->listen(AccessWasUnauthorized::class, 'App\Handlers\Events\Services\GameApi@onAccessWasUnauthorized');
		$events->listen(RateLimitWasExceeded::class, 'App\Handlers\Events\Services\GameApi@onRateLimitWasExceeded');
		$events->listen(RequestWasInvalid::class, 'App\Handlers\Events\Services\GameApi@onRequestWasInvalid');
		$events->listen(ServiceWasUnavailable::class, 'App\Handlers\Events\Services\GameApi@onServiceWasUnavailable');
		$events->listen(ServerHadAnError::class, 'App\Handlers\Events\Services\GameApi@onServerHadAnError');
		$events->listen(UnknownErrorOccurred::class, 'App\Handlers\Events\Services\GameApi@onUnknownErrorOccurred');
	}

}