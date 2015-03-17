define(['./Base'], (BaseController) ->
	AuthController = new BaseController()

	AuthController.login = () ->
		console.log('login')

	AuthController.register = () ->
		console.log('register')

	return AuthController
)