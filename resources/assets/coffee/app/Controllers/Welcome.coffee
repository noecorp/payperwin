class Welcome extends Controller
	actions: {
		start: (parameters) ->
			$('.todo').each((index, element) ->
				$todo = $(this)
				$todo.find('li').click((event) ->
					event.preventDefault()

					window.location.href = $todo.data('action')
				)
			)
	}

window.app.route('start','welcome.start')
window.app.controller('welcome',Welcome)