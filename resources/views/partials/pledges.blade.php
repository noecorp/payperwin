@if (!$feed->isEmpty())
	@foreach ($feed as $pledge)
		<li>
			<p class="lead">
				@if (isset($pledgesFor) && $pledgesFor == 'streamer')
					{{ $pledge->streamer->username }}, 
				@else
					{{ $pledge->owner->username }}, 
				@endif
				<span class="text-success">${{ sprintf("%0.2f",$pledge->amount) }}</span> <small>/ win</small>
			</p>
			<p>
				<small><em>pledged {{ $pledge->created_at->diffForHumans() }}</em></small>
			</p>
			@if ($pledge->message)
				<p>"{{ $pledge->message }}"</p>
			@endif
		</li>
	@endforeach
@else
	<li>
		<p>No pledges yet!</p>
	</li>
@endif
