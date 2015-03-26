@extends('app')

@section('content')

streamers.show

id: {{ $streamer->id }}

<h3>Stats</h3>
<ul>
	<li>Average pledge: ${{ $stats['average'] }}</li>
	<li>Highest pledge: ${{ $stats['highestPledge']->amount }}, {{ $stats['highestPledge']->owner->username }}</li>
</ul>

<h3>Pledge Feed</h3>
<ul>
	@foreach ($feed as $pledge)
		<li>{{ $pledge->owner->username }} with ${{ $pledge->amount }} per win. 
			@if ($pledge->message)
				<small>Message: {{ $pledge->message }}</small>
			@endif
		</li>
	@endforeach
</ul>

@if ($streamer->twitch_username)
	<div>
		<object type="application/x-shockwave-flash" height="323" width="520" data="https://www-cdn.jtvnw.net/swflibs/TwitchPlayer.swf?channel={{ $streamer->twitch_username }}" bgcolor="#000000">
		    <param name="allowFullScreen" value="true" />
		    <param name="allowScriptAccess" value="always" />
		    <param name="allowNetworking" value="all" />
		    <param name="movie" value="https://www-cdn.jtvnw.net/swflibs/TwitchPlayer.swf" />
		    <param name="flashvars" value="hostname=www.twitch.tv&amp;channel={{ $streamer->twitch_username }}&amp;auto_play=false&amp;start_volume=100" />
		</object>
	</div>
	<div>
		<iframe frameborder="0" scrolling="no" src="https://twitch.tv/{{ $streamer->twitch_username }}/chat" height="400" width="520"></iframe>
	</div>
@endif

@endsection