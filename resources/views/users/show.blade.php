@extends('app')

@section('content')
	<div class="row">
		<div class="col-xs-12">
			<h1>{{ $user->username }}<img class="avatar" src="{{ asset($user->avatar) }}"></h1>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<h2>Pledges</h2>

			@if (!$feed->isEmpty())
				<ul>
					
					@foreach ($feed as $pledge)
						<li>${{ $pledge->amount }} per win to {{ $pledge->streamer->username }}. 
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
							${{ $stats['average'] }}
						@endif
					</li>
					<li>Highest pledge: 
						@if (isset($stats['highestPledge']))
							${{ $stats['highestPledge']->amount }} to {{ $stats['highestPledge']->streamer->username }}
						@endif
					</li>
				</ul>
			@else
				<p>No stats yet!</p>
			@endif
		</div>
	</div>
@endsection