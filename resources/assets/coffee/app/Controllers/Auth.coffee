class Auth extends Controller
	actions: {
		login: (parameters) =>
			twitchClicked = false

			$twitch = $('#login-twitch')

			$twitch.click((event) ->
				if twitchClicked
					event.preventDefault()
					return false

				twitchClicked = true

				$twitch.after('&nbsp;<img src="/img/loading.gif"/>');
			)

		register: (parameters) =>
			twitchClicked = false

			$twitch = $('#register-twitch')

			$twitch.click((event) ->
				if twitchClicked
					event.preventDefault()
					return false

				twitchClicked = true

				$twitch.after('&nbsp;<img src="/img/loading.gif"/>');
			)
	}

window.app.route('auth/login','auth.login')
window.app.route('auth/register','auth.register')
window.app.controller('auth',Auth)
