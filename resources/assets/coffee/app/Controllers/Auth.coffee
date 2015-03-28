define(['./Base'], (BaseController) ->
	AuthController = new BaseController()

	AuthController.login = () ->
		$('[data-toggle="checkbox"]').radiocheck();

		twitchClicked = false

		$twitch = $('#login-twitch')

		$twitch.click((event) ->
			if twitchClicked
				event.preventDefault()
				return false

			twitchClicked = true

			$twitch.after('&nbsp;<img src="/img/loading.gif"/>');
		)

	AuthController.register = () ->
		twitchClicked = false

		$twitch = $('#register-twitch')

		$twitch.click((event) ->
			if twitchClicked
				event.preventDefault()
				return false

			twitchClicked = true

			$twitch.after('&nbsp;<img src="/img/loading.gif"/>');
		)

	return AuthController
)