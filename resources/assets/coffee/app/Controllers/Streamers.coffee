class Streamers extends Controller
	actions: {
		show: (parameters) ->
			$('[data-toggle=tooltip]').tooltip()

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

			$('#streamer-hide').click(() ->
				$('#streamer-stream').toggle()
			)
			
	}

window.app.route('streamers/:id','streamers.show')
window.app.controller('streamers',Streamers)