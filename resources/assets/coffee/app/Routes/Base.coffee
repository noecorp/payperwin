define((require) ->
	
	BaseRoute = (controller) ->
		this.controller = controller

	BaseRoute.prototype = {

		go: (routeArguments) ->

			throw 'Missing implementation'

	}

	return BaseRoute
)