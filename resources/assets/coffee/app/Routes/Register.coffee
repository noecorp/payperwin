define((require) ->
	controller = require('app/Controllers/Register')
	model = require('app/Models/User')

	# A fabricated API to show interaction of
	# common and specific pieces.
	controller.setModel(model)
	
	controller.render();
)