<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Illuminate\Contracts\Mail\Mailer;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\Cache\Repository as Cache;

class NotifyAboutNewStreamer extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * {@inheritdoc}
	 */
	protected $unique = true;

	/**
	 * Streamer model id.
	 *
	 * @var int
	 */
	protected $streamerId;

	/**
	 * Users repository implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * Mailer implementation.
	 *
	 * @var Mailer
	 */
	protected $mail;

	/**
	 * Create a new command instance.
	 *
	 * @param int $streamerId
	 *
	 * @return void
	 */
	public function __construct($streamerId)
	{
		parent::__construct($streamerId);

		$this->streamerId = $streamerId;
	}

	/**
	 * Execute the command.
	 *
	 * @param Users $users
	 * @param Mailer $mail
	 * @param Cache $cache
	 *
	 * @return void
	 */
	public function handle(Users $users, Mailer $mail, Cache $cache)
	{
		$this->users = $users;
		$this->mail = $mail;
		$this->cache = $cache;

		$this->start();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function work()
	{
		$streamer = $this->users->find($this->streamerId);

		if (!$streamer)
		{
			$this->delete();

			return;
		}

		$this->mail->send('emails.admin.streamer-active',compact('streamer'), function($message)
		{
			$message->to(config('mail.admin'))->subject('New Streamer Activated')->from(config('mail.from.address'),config('mail.from.name'));
		});

		$this->delete();
	}

}
