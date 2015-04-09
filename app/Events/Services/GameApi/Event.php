<?php namespace App\Events\Services\GameApi;

use App\Events\Event as BaseEvent;
use App\Contracts\Events\GameApi as GameApiEventInterface;

class Event extends BaseEvent implements GameApiEventInterface {

	/**
	 * The game that event is for.
	 *
	 * @var string
	 */
	protected $game;

	/**
	 * The url that the event is for.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Additional event info.
	 *
	 * @var array
	 */
	protected $info;

	/**
	 * Create a new event instance.
	 *
	 * @param string $game
	 * @param string $url
	 * @param array $info additional info dictionary
	 *
	 * @return void
	 */
	public function __construct($game, $url, array $info = array())
	{
		$this->game = $game;
		$this->url = $url;
		$this->info = $info;
	}

	/**
	 * {@inheritdoc}
	 */
	public function game()
	{
		return $this->game;
	}

	/**
	 * {@inheritdoc}
	 */
	public function url()
	{
		return $this->url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function info()
	{
		return $this->info;
	}

}
