@extends('app')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>{{ $user->username }}
				@if ($user->avatar)
					<img class="avatar" src="{{ asset($user->avatar) }}">
				@endif
			</h1>
			<hr/>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<h2>Pledges</h2>

			@if (!$feed->isEmpty())
				<ul>
					
					@foreach ($feed as $pledge)
						<li>${{ sprintf("%0.2f",$pledge->amount) }} per win to <a href="/streamers/{{ $pledge->streamer->id }}">{{ $pledge->streamer->username }}</a>. 
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
		<div class="col-xs-12 col-sm-6">
			<h2>Stats</h2>

			@if (!$feed->isEmpty())
				<ul>
					<li>Average pledge: 
						@if (isset($stats['average']))
							${{ sprintf("%0.2f",$stats['average']) }}
						@endif
					</li>
					<li>Highest pledge: 
						@if (isset($stats['highestPledge']))
							${{ sprintf("%0.2f",$stats['highestPledge']->amount) }} to <a href="/streamers/{{ $stats['highestPledge']->streamer->id }}">{{ $stats['highestPledge']->streamer->username }}</a>
						@endif
					</li>
				</ul>
			@else
				<p>No stats yet!</p>
			@endif
		</div>
	</div>
@endsection