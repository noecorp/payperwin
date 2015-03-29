class Users extends Controller
	actions: {
		show: (parameters) ->

		edit: (parameters) ->
			$streamerSwitch = $('#streamer-on')

			if $streamerSwitch
				$streamerSwitch.bootstrapSwitch({
					onSwitchChange: (event,state) ->
						if state == true
							$.ajax({
								type: 'put',
								url: '/users/'+parameters.id,
								data: {
									'streamer': 1,
									'_token': window.app.support.csrfToken()
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
				})

			$('[data-toggle="select"]').select2()

			$summonerNameField = $('#streaming-summoner-name')
			$summonerRegionField = $('#streaming-region')
			$streamingSubmitButton = $('#streaming-form-submit')
			$matchInfo = $('#streaming-match-info')
			$summonerIdField = $('#streaming-summoner-id')
			searching = false

			$searchButton = $('#streaming-search');
			$searchButton.click((event) ->
				event.preventDefault()
				if (searching)
					return false

				$summonerNameField.parents('.form-group').first().removeClass('has-error')
				$summonerIdField.val('')
				$streamingSubmitButton.prop('disabled',true)
				$matchInfo.hide()
				$matchInfo.find('ul').empty()

				if !$summonerNameField.val()
					$summonerNameField.parents('.form-group').first().addClass('has-error')
					return false

				searching = true
				buttonValue = $searchButton.html()
				$searchButton.html('<img src="/img/loading.gif"/>')
				$searchButton.prop('disabled',true)

				$.ajax({
					type: 'get',
					url: '/clients/league/summoner/'+$summonerNameField.val()+'/'+$summonerRegionField.val(),
					dataType: 'json'
				})
				.done((data,textStatus,jqXHR) ->
					$streamingSubmitButton.prop('disabled',false)
					$matchInfo.show()
					if data.match.win
						$matchInfo.find('ul').append('<li>Result: Won</li>')
					else
						$matchInfo.find('ul').append('<li>Result: Lost</li>')
					$matchInfo.find('ul').append('<li>Played as: '+data.match.champion+'</li>')
					$matchInfo.find('ul').append('<li>'+data.match.ago+'</li>')
					$summonerIdField.val(data.summoner.id)
				).fail((jqXHR, textStatus, errorThrown) ->
					if jqXHR.responseJSON?
						console.log(jqXHR.responseJSON.error, jqXHR.status)
					else
						console.log(jqXHR.responseText,jqXHR.status)
				).always(() ->
					searching = false
					$searchButton.html(buttonValue)
					$searchButton.prop('disabled',false)
				)
			)
	}

window.app.route('users/:id/edit','users.edit')
window.app.route('users/:id','users.show')
window.app.controller('users',Users)