@extends('app')

@section('content')

users.show

id: {{ $user->id }}

<h3>Stats</h3>
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

<h3>Pledge Feed</h3>
<ul>
	@foreach ($feed as $pledge)
		<li>${{ $pledge->amount }} per win to {{ $pledge->streamer->username }}. 
			@if ($pledge->message)
				<small>Message: {{ $pledge->message }}</small>
			@endif
		</li>
	@endforeach
</ul>

@endsection