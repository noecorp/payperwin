class Controller
	run: (action, parameters) ->
		@actions[action](parameters)

class Support
	csrfToken: () ->
		if $('input[name="_token"]').first()?
			return $('input[name="_token"]').first().val()
		else
			return null

class App
	controllers: {}
	routes: []
	config: {
		foo : {
			bar : 'baz'
		}
	}

	support: new Support()
	
	# Allow dot-syntax nesting of keys.
	get: (key) ->
		keys = key.split('.')

		c = @config

		loop
			k = keys.shift()
			c = c[k]
			break unless (keys.length)

		return c

	init: () ->
		segments = window.location.pathname.split('/')
		segments.shift() # We don't care about the base domain

		found = null
		parameters = null

		for route in @routes
			
			routeSegments = if route.path == '/' then [''] else route.path.split('/')

			continue if routeSegments.length > segments.length

			parameters = {}
			matched = true

			for segment, index in routeSegments
				if segment != segments[index] && segment.substring(0,1) != ':'
					matched = false
					break
				if segment.substring(0,1) == ':'
					parameters[segment.substring(1)] = segments[index]

			if matched
				found = route
				break

		if found
			action = found.action.split('.')
			controller = new @controllers[action[0]]()
			controller.run action[1], parameters

	controller: (name, controller) ->
		@controllers[name] = controller

	route: (routePath, controllerAction) ->
		@routes.push({
			path: routePath,
			action: controllerAction
		})

window.app = new App()