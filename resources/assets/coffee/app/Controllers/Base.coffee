define((require) ->

	# Load generic UI-related logic
	UI = require('app/Support/UI')

	BaseController = () ->

	
	BaseController.prototype = {
		
		setModel: (model) ->
			this.model = model
			
	}

	return BaseController
)