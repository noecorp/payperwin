define(['./Base','app/Support/Request','app/Support/Lib'], (BaseController,Request,Lib) ->
	UsersController = new BaseController()

	UsersController.show = (id) ->
		console.log('show')

	UsersController.edit = (id) ->
		console.log('edit')
		$streamerRadio = $('#streamer-on')

		if $streamerRadio
			$streamerRadio.click(() ->

				$.ajax({
					type: 'put',
					url: '/users/'+id,
					data: {
						'streamer': 1,
						'_token': Lib.csrfToken()
					},
					dataType: 'json'
				})
				.done((data,textStatus,jqXHR) ->
					if jqXHR.status == 200
						return window.location.reload()

					console.log(data, jqXHR.status)
				).fail((jqXHR, textStatus, errorThrown) ->
					if jqXHR.responseJSON?
						console.log(jqXHR.responseJSON.error, jqXHR.status)
					else
						console.log(jqXHR.status)
				)
			)

	return UsersController
)