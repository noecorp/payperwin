class Streamers extends Controller
	actions: {
		show: (parameters) =>
			model = $('[data-model]').first().data()
			update = () ->
				$heading = $('#loading')
				value = $heading.html()
				$heading.html('<img src="/img/loading.gif"/>')
				$pledgeList = $('#pledges-list')

				$.ajax({
					type: 'get',
					url: '/streamers/'+model.id+'/pledges',
					dataType: 'json'
				})
				.done((data,textStatus,jqXHR) ->
					pledges = data.pledges
					if pledges
						$pledgeList.empty()
						for pledge in pledges
							$pledgeList.append('<li><a href="/users/'+pledge.user.id+'">'+pledge.user.username+'</a> with '+pledge.amount+' per '+pledge.type+'.'+(if pledge.message then '<br><blockquote>'+pledge.message+'</blockquote>' else '')+'</li>')
				).fail((jqXHR, textStatus, errorThrown) ->
					
				).always(() ->
					$heading.html(value)
					queueUpdate()
				)
			queueUpdate = () ->
				window.setTimeout(() ->
					update()
				, 10000)
			queueUpdate()


			$streamerChat = $('#streamer-chat')
			$streamerChat.html('
				<iframe frameborder="0" scrolling="no" src="https://twitch.tv/'+$streamerChat.attr('data-username')+'/chat" height="400" width="100%"></iframe>
			')

			$streamerVideo = $('#streamer-video')
			$streamerVideo.html('
				<object type="application/x-shockwave-flash" height="323" width="520" data="https://www-cdn.jtvnw.net/swflibs/TwitchPlayer.swf?channel='+$streamerVideo.attr('data-username')+'" bgcolor="#000000">
				    <param name="allowFullScreen" value="true" />
				    <param name="allowScriptAccess" value="always" />
				    <param name="allowNetworking" value="all" />
				    <param name="movie" value="https://www-cdn.jtvnw.net/swflibs/TwitchPlayer.swf" />
				    <param name="flashvars" value="hostname=www.twitch.tv&amp;channel='+$streamerVideo.attr('data-username')+'&amp;auto_play=false&amp;start_volume=100" />
				</object>
			')
			$object = $streamerVideo.find('object').first()
			$object.attr('data-aspectRatio', $object.height() / $object.width())
				.removeAttr('width')
				.removeAttr('height')

			resize = () ->
				$object.width($streamerVideo.parent().width())
				.height($streamerVideo.parent().width() * $object.attr('data-aspectRatio'))

			$(window).resize(() ->
				resize()
			)

			resize()

			$streamerHide = $('#streamer-hide')
			$streamerStream = $('#streamer-stream')
			$streamerHide.click(() ->
				toggle = $streamerHide.attr('data-toggle')
				previous = $streamerHide.text()
				$streamerHide.text(toggle)
				$streamerHide.attr('data-toggle',previous)

				if $streamerStream.is(':hidden')
					$streamerHide.removeClass('btn-info')
					$streamerHide.addClass('btn-default')
					$.cookie('hide-stream',0,{expires:90,path:'/'})
				else
					$streamerHide.removeClass('btn-default')
					$streamerHide.addClass('btn-info')
					$.cookie('hide-stream',1,{expires:90,path:'/'})

				$streamerStream.toggle()
			)

			showStreamDefault = $.cookie('hide-stream',Number);
			if showStreamDefault
				$streamerHide.click()

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
					showMessages($profileResults.children().first(),['Pledge created!'], false)
					window.setTimeout(() ->
						$pledgeForm.parents('.modal').first().modal('hide')
						$pledgeForm[0].reset()
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
