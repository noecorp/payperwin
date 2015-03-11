define((require) ->
	controller = require('app/Controllers/Deposits')
	model = require('app/Models/Deposit')

	# A fabricated API to show interaction of
	# common and specific pieces.
	controller.setModel(model)
	
	controller.render();
)