@extends('app')

@section('title', $streamer->username)

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>{{ $streamer->username }}<img class="avatar" src="{{ asset($streamer->avatar) }}"></h1>
			@if ($streamer->live)
				<p class="text-primary"><strong>Live now!</strong></p>
			@else
				<p class="text-muted"><strong>Offline</strong></p>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			@if ($auth->guest())
				<a href="/auth/login" class="btn btn-lg btn-success btn-block">Start a Pledge!</a>
			@else
				<button title="This will bring up a form." data-placement="top" data-toggle="tooltip" class="btn btn-lg btn-success btn-block">Start a Pledge!</button>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-md-6">
			<h2>Pledges</h2>
	
			@if (!$feed->isEmpty())
				<ul>
					@foreach ($feed as $pledge)
						<li>{{ $pledge->owner->username }} with ${{ $pledge->amount }} per win. 
							@if ($pledge->message)
								<small>Message: {{ $pledge->message }}</small>
							@endif
						</li>
					@endforeach
				</ul>
			@else
				<p>No pledges yet!</p>
			@endif
		</div>
		<div class="col-xs-12 col-md-6">
			<h2>Stats</h2>

			@if (!$feed->isEmpty())
				<ul>
					<li>Average pledge: ${{ $stats['average'] }}</li>
					<li>Highest pledge: ${{ $stats['highestPledge']->amount }}, {{ $stats['highestPledge']->owner->username }}</li>
				</ul>
			@else
				<p>No stats yet!</p>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h2>Stream</h2>
			@if ($streamer->twitch_username)
			
			<p><a href="javascript:;" id="streamer-hide">Hide</a></p>
			
			<div class="row" id="streamer-stream">
				<div class="col-xs-12 col-sm-8">
					<div id="streamer-video" data-username="{{ $streamer->twitch_username }}">
						<img src="/img/loading.gif">
					</div>
				</div>
				<div class="col-xs-12 col-sm-4">
					<div id="streamer-chat" data-username="{{ $streamer->twitch_username }}">
				</div>
			</div>
			@endif
		</div>
	</div>
@endsection