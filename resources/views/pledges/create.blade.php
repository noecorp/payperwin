@extends('app')

@section('content')

pledges.create

<form method="POST" action="/pledges">
	<div class="messages">
		{{ $errors->first() }}
	</div>

	<input type="hidden" name="_token" value="{{ csrf_token() }}" />
	<input type="hidden" name="streamer_id" value="1" />
	<input type="hidden" name="type" value="{{ $guru->win() }}" />
	<input type="hidden" name="user_id" value="{{ $auth->user()->id }}" />

	<label>Amount: <input type="text" name="amount" placeholder="$0.00" /></label>
	<label>Message: <textarea name="message"></textarea></label>
	<label>End date: <input type="date" name="end_date"></label>

	<input type="submit" value="Start"/>
</form>

@if ($streamer)
	Streamer: {{ $streamer->id }}
@endif

@endsection