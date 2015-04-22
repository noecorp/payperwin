<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Illuminate\Contracts\Mail\Mailer;
use App\Contracts\Repository\Users;

class NotifyAboutNewStreamer extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * Streamer model id.
	 *
	 * @var int
	 */
	protected $streamerId;

	/**
	 * Create a new command instance.
	 *
	 * @param int $streamerId
	 *
	 * @return void
	 */
	public function __construct($streamerId)
	{
		$this->streamerId = $streamerId;
	}

	/**
	 * Execute the command.
	 *
	 * @param Users $users
	 * @param Mailer $mail
	 *
	 * @return void
	 */
	public function handle(Users $users, Mailer $mail)
	{
		$streamer = $users->find($this->streamerId);

		$mail->send('emails.admin.streamer-active',compact('streamer'), function($message)
		{
			$message->to(config('mail.admin'))->subject('New Streamer Activated')->from(config('mail.from.address'),config('mail.from.name'));
		});

		$this->delete();
	}

}
