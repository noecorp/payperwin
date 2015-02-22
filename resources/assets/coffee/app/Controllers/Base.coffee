define((require) ->

	# Load generic UI-related logic
	UI = require('app/Support/UI')

	ControllerBase = (id) ->
		this.id = id;

	ControllerBase.prototype = {
		
		setModel: (model) ->
			this.model = model
		,
		render: (bodyDom) ->
			bodyDom.prepend('<h1>Controller ' + this.id + ' says "' + this.model.getTitle() + '"</h1>');
			
	}

	return ControllerBase
)