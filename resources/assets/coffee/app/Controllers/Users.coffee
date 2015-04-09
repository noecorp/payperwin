class Users extends Controller
	actions: {
		show: (parameters) ->

		edit: (parameters) ->

			showMessages = ($ul,messages,error) ->
				newClass = if error == true then 'alert-danger' else 'alert-success'
				$ul.empty()
				for message in messages
					$ul.append('<li>'+message+'</li>')
				$ul.parent().removeClass('alert-danger').removeClass('alert-success').addClass(newClass)
				$ul.parent().show()
				$ul.scrollintoview({offset:200})

			$profileForm = $('#profile-form')
			
			profileFormSubmitting = false

			$profileForm.submit((event) ->
				event.preventDefault()
				if (profileFormSubmitting)
					return false
				profileFormSubmitting = true

				$profileSubmit = $('#profile-submit')
				$profileSubmit.prop('disabled',true)
				$profileResults = $('#profile-results')
				$profileResults.hide()

				$.ajax({
					type: 'put',
					url: $profileForm.attr('action'),
					data: $profileForm.serialize(),
					dataType: 'json'
				})
				.done((data,textStatus,jqXHR) ->
					if jqXHR.status == 200
						return showMessages($profileResults.children().first(),['Updated!'],false)
				).fail((jqXHR, textStatus, errorThrown) ->
					if jqXHR.responseJSON?
						if jqXHR.status == 422
							errors = []
							for error,values of jqXHR.responseJSON
								errors = errors.concat(values)
							return showMessages($profileResults.children().first(),errors,true)
						else if jqXHR.responseJSON.error?
							return showMessages($profileResults.children().first(),[jqXHR.responseJSON.error],true)
					
					return showMessages($profileResults.children().first(),['An error occurred. Let us know if this keeps happening.'],true)
				).always(() ->
					profileFormSubmitting = false
					$profileSubmit.prop('disabled',false)
				)

				return false
			)

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

			streamingFormSubmitting = false
			$streamingForm = $('#streaming-form')

			$streamingForm.submit((event) ->
				event.preventDefault()
				if (streamingFormSubmitting)
					return false
				streamingFormSubmitting = true

				$streamingSubmitButton.prop('disabled',true)
				$streamingResults = $('#streaming-results')
				$streamingResults.hide()

				$.ajax({
					type: 'put',
					url: $streamingForm.attr('action'),
					data: $streamingForm.serialize(),
					dataType: 'json'
				})
				.done((data,textStatus,jqXHR) ->
					if jqXHR.status == 200
						showMessages($streamingResults.children().first(),['Updated!'],false)
						return window.location.reload()
				).fail((jqXHR, textStatus, errorThrown) ->
					if jqXHR.responseJSON?
						if jqXHR.status == 422
							errors = []
							for error,values of jqXHR.responseJSON
								errors = errors.concat(values)
							return showMessages($streamingResults.children().first(),errors,true)
						else if jqXHR.responseJSON.error?
							return showMessages($streamingResults.children().first(),[jqXHR.responseJSON.error],true)
					
					return showMessages($streamingResults.children().first(),['An error occurred. Let us know if this keeps happening.'],true)
				).always(() ->
					streamingFormSubmitting = false
					$streamingSubmitButton.prop('disabled',false)
				)

				return false
			)
	}

window.app.route('users/:id/edit','users.edit')
window.app.route('users/:id','users.show')
window.app.controller('users',Users)