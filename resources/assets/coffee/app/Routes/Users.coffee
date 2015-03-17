define(['./Base','app/Controllers/Users'], (BaseRoute,UsersController) ->
	
	UsersRoute = new BaseRoute()

	UsersRoute.go = (routeArguments) ->

		if routeArguments.action == 'edit' && typeof routeArguments.id != "undefined"
			UsersController.edit(routeArguments.id)
		else if typeof routeArguments.action == "undefined" && typeof routeArguments.id != "undefined"
			UsersController.show(routeArguments.id)
		else if routeArguments.length == 0
			UsersController.index()
		else
			throw 'Route not found.'

	return UsersRoute
)