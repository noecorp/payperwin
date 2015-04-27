<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use App\Contracts\Repository\Users;
use Illuminate\Contracts\Mail\Mailer;

class SendEmailConfirmationRequest extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * User model id.
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * Create a new command instance.
	 *
	 * @param int $userId
	 *
	 * @return void
	 */
	public function __construct($userId)
	{
		$this->userId = $userId;
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
		try
		{
			$user = $users->find($this->userId);

			$mail->send('emails.confirm',['username' => $user->username, 'code' => $user->confirmation_code], function($message) use ($user)
			{
				$message->to($user->email)->subject('Welcome! One more thing...')->from(config('mail.from.address'),config('mail.from.name'));
			});

			$this->delete();
		}
		catch (\Exception $e)
		{
			$this->release(300);
		}
	}

}
