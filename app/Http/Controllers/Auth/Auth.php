<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Http\Request;
use App\Contracts\Repository\Users;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Routing\Redirector as Redirect;
use Intervention\Image\ImageManager;

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

		$this->middleware('guest', ['except' => ['getLogout','getWith','getProvider']]);
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
		$this->auth->login($this->users->create($request->all()),true);

		return $this->redirect->to('/start');
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
			return $this->redirect->intended('/auth/login');
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

			$existing = ($provider == 'twitch') ? $this->users->havingTwitchId($user->getId())->find() : $this->users->havingFacebookId($user->getId())->find();

			if ($existing)
			{
				if ($this->auth->guest())
					return $this->loginExisting($existing);
				else
					return $this->redirect->to('/users/'.$existing->id);
			}
			else
			{

				if ($this->auth->guest())
				{
					// What if the username is already taken in the system?

					if ($provider == 'twitch')
					{
						$existing = $this->users->havingUsername($user->getNickname())->find();
						
						if ($existing)
						{
							// We'll set it to null for now and require that it be set later
							$user->nickname = null;
						}
					}

					$newUser = $this->createWithSocial($provider, $user);

					return $this->loginExisting($newUser, '/start');
				}
				else
				{
					$this->users->update($this->auth->user(),[
						'twitch_id' => $user->getId(),
						'twitch_username' => $user->getNickname(),
						'avatar' => $this->getLocalImagePath($user)
					]);

					return $this->redirect->to('/users/'.$this->auth->user()->id.'/edit');
				}
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

		return $this->redirect->to($to)->withErrors(['social'=>$error]);
	}

	protected function loginExisting(\App\Models\User $user, $uri = null)
	{
		$this->auth->login($user, true);

		if ($uri)
			return $this->redirect->to($uri);

		if ($this->auth->user()->streamer && !$this->auth->user()->twitch_id || !$this->auth->user()->summoner_id)
		{
			return $this->redirect->to('/users/'.$user->id.'/edit');
		}
		else if ($this->auth->user()->streamer)
		{
			return $this->redirect->to('/streamers/'.$user->id);
		}
		else
		{
			return $this->redirect->to('/users/'.$user->id);	
		}
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

		$data['avatar'] = $this->getLocalImagePath($user);

		return $this->users->create($data);

		### -> flash something?
		### -> redirect to choosing username and password
		### -> if email null, account locked until email set
	}

	protected function getImageManager()
	{
		return new ImageManager(config('image'));
	}

	protected function getLocalImagePath(\Laravel\Socialite\Contracts\User $user)
	{
		if (!$user->getAvatar())
		{
			return null;
		}

		$manager = $this->getImageManager();

		$url = $user->getAvatar();

		$img = $manager->make($url)->widen(100);

		$hash = md5($user->getEmail());

		$folder = 'avatars/'.substr($hash, 0, 2).'/'.substr($hash, 2, 2);

		if (!is_dir($folder))
			mkdir(public_path($folder), 0775, true);

		$path = $folder.'/'.time().'.'.preg_replace('/^.*\.(jpg|jpeg|png|gif)$/i', '$1', $url);

		$img->save($path,100);

		chmod(public_path($path), 0774);

		return $path;
	}

}
