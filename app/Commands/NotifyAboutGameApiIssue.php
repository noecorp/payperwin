<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Illuminate\Contracts\Mail\Mailer;

class NotifyAboutGameApiIssue extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue;

	/**
	 * Issue type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Issue game.
	 *
	 * @var string
	 */
	protected $game;

	/**
	 * Issue url.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Issue info.
	 *
	 * @var array
	 */
	protected $info;

	/**
	 * Create a new command instance.
	 *
	 * @param string $type
	 * @param string $game
	 * @param string $url
	 * @param array $info
	 *
	 * @return void
	 */
	public function __construct($type, $game, $url, array $info = array())
	{
		$this->type = $type;
		$this->game = $game;
		$this->url = $url;
		$this->info = $info;
	}

	/**
	 * Execute the command.
	 *
	 * @param Mailer $mail
	 *
	 * @return void
	 */
	public function handle(Mailer $mail)
	{
		$type = $this->type;
		$game = $this->game;
		$url = $this->url;
		$info = $this->info;

		$mail->send('emails.admin.game-api-issue',compact('type','game','url','info'), function($message)
		{
			$message->to(config('mail.admin'))->subject('Game API Issue')->from(config('mail.from.address'),config('mail.from.name'));
		});

		$this->delete();
	}

}
