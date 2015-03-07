<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Http\Request;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Routing\Redirector as Redirect;

class Auth extends Controller {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The Users Repository implementation.
	 *
	 * @var Users
	 */
	protected $users;

	/**
	 * The Socialite Factory implementation.
	 *
	 * @var Socialite
	 */
	protected $socialite;

	/**
	 * The View Factory implementation.
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * The Redirector implementation.
	 *
	 * @var Redirect
	 */
	protected $redirect;


	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  Guard  $auth
	 * @param  Users  $users
	 * @param  Socialite  $socialite
	 * @param  View  $view
	 * @param  Redirect  $redirect
	 *
	 * @return void
	 */
	public function __construct(Guard $auth, Users $users, Socialite $socialite, View $view, Redirect $redirect)
	{
		$this->auth = $auth;
		$this->users = $users;
		$this->socialite = $socialite;
		$this->view = $view;
		$this->redirect = $redirect;

		$this->middleware('guest', ['except' => 'getLogout']);
	}

	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getRegister()
	{
		return $this->view->make('auth.register');
	}

	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \App\Http\Requests\Register  $request
	 * @param  Redirect  $redirect
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function postRegister(\App\Http\Requests\Register $request)
	{
		$this->auth->login($this->users->create($request->all()));

		return $this->redirect->to('/auth/register');
	}

	/**
	 * Show the application login form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogin()
	{
		return $this->view->make('auth.login');
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  \App\Http\Requests\Login  $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function postLogin(\App\Http\Requests\Login $request)
	{
		$credentials = $request->only('email', 'password');

		if ($this->auth->attempt($credentials, $request->has('remember')))
		{
			return $this->redirect->intended('/dashboard');
		}

		return $this->redirect->to('/auth/login')
					->withInput($request->only('email', 'remember'))
					->withErrors([
						'email' => 'These credentials do not match our records.',
					]);
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogout()
	{
		$this->auth->logout();

		return $this->redirect->to('/');
	}

	/**
	 * Redirects to appropriate social authentication site.
	 *
	 * @param  Request  $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function getWith(Request $request, $provider)
	{
		return $this->socialite->with($provider)->redirect($request->has('rerequest'));
	}

	/**
	 * Processes callback from social authentication site.
	 *
	 * @param  Request  $request
	 * @param  string $provider
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getProvider(Request $request, $provider)
	{
		$error = $request->get('error');

		if ($error)
		{
			return $this->redirectWithSocialError($error);
		}
		else
		{
			$user = $this->socialite->with($provider)->user();

			if (!$user->email)
			{
				return $this->redirectWithSocialError('email');
			}

			$existing = ($provider == 'twitch') ? $this->users->withTwitchId($user->getId()) : $this->users->withFacebookId($user->getId());

			if ($existing)
			{
				return $this->loginExisting($existing);
			}
			else
			{
				// What if the username is already taken in the system?

				if ($provider == 'twitch')
				{
					$existing = $this->users->withUsername($user->getNickname());
					
					if ($existing)
					{
						// We'll set it to null for now and require that it be set later
						$user->nickname = null;
					}
				}

				$newUser = $this->createWithSocial($provider, $user);

				return $this->loginExisting($newUser, '/profile');
			}
		}
	}

	protected function redirectWithSocialError($error)
	{
		$to = 'auth/register';

		if ($error == 'access_denied') $error = 'We need the requested access to your profile to continue.';

		if ($error == 'email')
		{
			$error = 'We do need your email though...';
			$to .= '?rerequest=1';
		}

		return $this->redirect->to($uri)->withErrors(['social'=>$error]);
	}

	protected function loginExisting(\App\Models\User $user, $uri = null)
	{
		$this->auth->login($user);

		if ($uri)
			return $this->redirect->to($uri);

		return $this->redirect->to('/dashboard');
	}

	protected function createWithSocial($provider, \Laravel\Socialite\Contracts\User $user)
	{
		$data = [
			'username' => $user->getNickname(),
			'email' => $user->getEmail(),
		];

		if ($provider == 'twitch')
		{
			$data['twitch_id'] = $user->getId();
			$data['twitch_username'] = $user->getNickname();
		}
		else
		{
			$data['facebook_id'] = $user->getId();
		}

		return $this->users->create($data);

		### -> flash something?
		### -> redirect to choosing username and password
		### -> if email null, account locked until email set
	}

}
