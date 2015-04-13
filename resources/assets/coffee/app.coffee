# Extend JS String with repeat method
String.prototype.repeat = (num) ->
	return new Array(Math.round(num) + 1).join(this)

class Controller
	run: (action, parameters) ->
		@actions[action](parameters)

class Support
	csrfToken: () ->
		if $('input[name="_token"]').first()?
			return $('input[name="_token"]').first().val()
		else
			return null
	every: () ->
		# Enable navbar deposit action
		$('#nav-deposit').click(() ->
			window.location.href = $(this).attr('data-href')
		)

		# Focus state for append/prepend inputs
		$('.input-group').on('focus', '.form-control', () ->
			$(this).closest('.input-group, .form-group').addClass('focus')
		).on('blur', '.form-control', () ->
			$(this).closest('.input-group, .form-group').removeClass('focus')
		)

		# Enable checkbox styling
		$('[data-toggle="checkbox"]').radiocheck();

		# Enable tooltips
		$('[data-toggle=tooltip]').tooltip()

		# Enable form select styling
		$('[data-toggle="select"]').select2()

		# Add segments to a slider
		$.fn.addSliderSegments = () ->
			return this.each(() ->
				$this = $(this)
				option = $this.slider('option')
				amount = (option.max - option.min)/option.step
				orientation = option.orientation

				if 'vertical' == orientation
					output = ''
					for i in [1..amount-1] by 1
						output += '<div class="ui-slider-segment" style="top:' + 100 / amount * i + '%;"></div>'
					$this.prepend(output)
				else
					segmentGap = 100 / (amount) + '%'
					segment = '<div class="ui-slider-segment" style="margin-left: ' + segmentGap + ';"></div>'
					$this.prepend(segment.repeat(amount - 1))
			)

		$sliders = $(".slider")
		$sliders.each(() ->
			$this = $(this)
			$this.slider({
				value: $this.attr('data-value'),
				min: $this.attr('data-min'),
				max: $this.attr('data-max'),
				step: $this.attr('data-step'),
				orientation: $this.attr('data-orientation'),
				range: 'min'
			}).addSliderSegments()
		)

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

	controller: (name, controller) ->
		@controllers[name] = controller

	route: (routePath, controllerAction) ->
		@routes.push({
			path: routePath,
			action: controllerAction
		})

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

		@support.every()

window.app = new App()
