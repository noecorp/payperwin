class Streamers extends Controller
	actions: {
		show: (parameters) =>
			$streamerPledge = $('#streamer-pledge')
			if $streamerPledge.length
				$streamerPledge.click(() ->
					$('#'+$streamerPledge.attr('data-modal')).modal('show')
				)

			$('#streamer-pledge-optional').click(() ->
				$('#streamer-pledge-extras').toggle()
			)

			pledgeFormSubmitting = false
			
			$pledgeForm = $('#streamer-pledge-form')
			$pledgeFormSubmit = $('#streamer-pledge-submit')

			showMessages = ($ul,messages,error) ->
				newClass = if error == true then 'alert-danger' else 'alert-success'
				$ul.empty()
				for message in messages
					$ul.append('<li>'+message+'</li>')
				$ul.parent().removeClass('alert-danger').removeClass('alert-success').addClass(newClass)
				$ul.parent().show()

			$pledgeForm.submit((event) ->
				event.preventDefault()
				if pledgeFormSubmitting
					return false;

				pledgeFormSubmitting = true
				$pledgeFormSubmit.prop('disabled',true)
				buttonValue = $pledgeFormSubmit.html()
				$pledgeFormSubmit.html('<img src="/img/loading.gif"/>')

				$profileResults = $('#streamer-pledge-results')
				$profileResults.hide()

				$.ajax({
					type: $pledgeForm.attr('method'),
					url: $pledgeForm.attr('action'),
					data: $pledgeForm.serialize(),
					dataType: 'json'
				})
				.done((data,textStatus,jqXHR) ->
					showMessages($profileResults.children().first(),['Pledge created! Refreshing...'], false)
					window.setTimeout(() ->
						window.location.reload()
					, 2000)
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
					pledgeFormSubmitting = false
					$pledgeFormSubmit.prop('disabled',false)
					$pledgeFormSubmit.html(buttonValue)
				)
			)

			$pledgeFormSubmit.click((event) ->
				event.preventDefault()
				$pledgeForm.submit()
				return false
			)
			
	}

window.app.route('streamers/:id','streamers.show')
window.app.controller('streamers',Streamers)
