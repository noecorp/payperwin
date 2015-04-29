<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Cache\Repository as Cache;

class SendEmailConfirmationRequest extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * {@inheritdoc}
	 */
	protected $unique = true;

	/**
	 * User model id.
	 *
	 * @var int
	 */
	protected $userId;

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
	 * @param int $userId
	 *
	 * @return void
	 */
	public function __construct($userId)
	{
		parent::__construct($userId);

		$this->userId = $userId;
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
		$user = $this->users->find($this->userId);

		if (!$user)
		{
			$this->delete();

			return;
		}

		$this->mail->send('emails.confirm',['username' => $user->username, 'code' => $user->confirmation_code], function($message) use ($user)
		{
			$message->to($user->email)->subject('Welcome! One more thing...')->from(config('mail.from.address'),config('mail.from.name'));
		});

		$this->delete();
	}

}
