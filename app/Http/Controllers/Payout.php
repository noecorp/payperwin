<?php namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory as View;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Contracts\Mail\Mailer;
use App\Contracts\Service\Acidifier;
use App\Contracts\Repository\Users;
use App\Contracts\Repository\Transactions as TransactionsInterface;
use App\Contracts\Service\Gurus\Transaction as TransactionGuru;
use App\Http\Requests\SubmitPayout;

class Payout extends Controller {

	/**
	 * View factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Authentication implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Redirector instance.
	 *
	 * @var Redirect
	 */
	protected $redirect;

	/**
	 * Create a new controller instance.
	 *
	 * @param View $view
	 * @param Guard $auth
	 * @param Redirect $redirect
	 *
	 * @return void
	 */
	public function __construct(View $view, Guard $auth, Redirect $redirect)
	{
		$this->view = $view;
		$this->auth = $auth;
		$this->redirect = $redirect;
		
		$this->middleware('auth');
		$this->middleware('start');
	}

	/**
	 * Show the payout index screen.
	 *
	 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getIndex()
	{
		if (!$this->auth->user()->streamer)
		{
			return $this->redirect->to('/dashboard');
		}
		else
		{
			return $this->view->make('payout.index');
		}
	}

	/**
	 * Process the payout form.
	 *
	 * @param SubmitPayout $request
	 * @param Mailer $mail
	 * @param Acidifier $acid
	 * @param Users $users
	 * @param TransactionsInterface $transactions
	 * @param TransactionGuru $guru
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postIndex(SubmitPayout $request, Mailer $mail, Acidifier $acid, Users $users, TransactionsInterface $transactions, TransactionGuru $guru)
	{
		return $acid->transaction(function() use ($request, $mail, $users, $transactions, $guru)
		{
			$users->quietly()->increment($this->auth->user(), 'earnings', - $request->get('amount'));

			$transactions->create([
				'transaction_type' => $guru->streamerPaidOut(),
				'amount' => $request->get('amount'),
				'user_id' => $this->auth->user()->id,
				'source' => 0,
				'reference' => null,
				'pledge_id' => null,
				'username' => null
			]);

			$redirect = $this->redirect->back();

			$net = (floatval($request->get('amount')) * (1 - 2.9 / 100) - 0.30) * (1 - $this->auth->user()->commission / 100);

			$mail->send('emails.admin.payout',['username' => $this->auth->user()->username, 'email' => $request->get('email'), 'amount' => $request->get('amount'), 'net' => $net], function($message)
			{
				$message->to(config('mail.admin'))->subject('Payout Request')->from(config('mail.from.address'),config('mail.from.name'));
			});

			if (!count($mail->failures()))
			{
				$redirect->withInput()->with('success', 'Your request has been received. We will send you a confirmation email shortly. If you do not hear from us within a day, please let us know either with the support system (the link in the corner) or by emailing gg@payperwin.gg');
			}
			else
			{
				$redirect->with('error', 'Something went wrong while processing your request. Please contact us either with the support system (the link in the corner) or by emailing gg@payperwin.gg');
			}

			return $redirect;
		});
	}

}
