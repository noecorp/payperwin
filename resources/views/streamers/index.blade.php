@extends('app')

@section('content')

streamers.index

<h2>Live</h2>

<ul>
	@if (!$live->isEmpty())
		@foreach ($live as $streamer)
			<li><a href="/streamer/{{ $streamer->id }}">{{ $streamer->username }}</a></li>
		@endforeach
	@else
		<li> - </li>
	@endif
</ul>

<h2>Streamers</h2>

<ul>
	@foreach ($notLive as $streamer)
		<li><a href="/streamers/{{ $streamer->id }}">{{ $streamer->username }}</a></li>
	@endforeach
</ul>

@endsection