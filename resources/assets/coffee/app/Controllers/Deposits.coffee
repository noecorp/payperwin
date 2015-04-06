class Deposits extends Controller
	actions: {
		create: (parameters) ->

	}

window.app.route('deposits/create','deposits.create')
window.app.controller('deposits',Deposits)