define(['./Base','app/Controllers/Deposits'], (BaseRoute,DepositsController) ->
	
	DepositsRoute = new BaseRoute()

	DepositsRoute.go = (routeArguments) ->

		if routeArguments.action == 'create'
			DepositsController.create()
		else
			throw 'Route not found.'

	return DepositsRoute
)