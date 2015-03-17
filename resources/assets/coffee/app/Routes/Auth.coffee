define(['./Base','app/Controllers/Auth'], (BaseRoute,AuthController) ->
	
	AuthRoute = new BaseRoute()

	AuthRoute.go = (routeArguments) ->
		
		if routeArguments.action == 'login'
			AuthController.login()
		else if routeArguments.action == 'register' 
			AuthController.register()
		else
			throw 'Route not found.'

	return AuthRoute
)