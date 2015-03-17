@extends('app')

@section('content')

users.edit

{{ $user->id }}

<h3>Streamer profile</h3>

@if (!$user->streamer)

<form>
<input type="hidden" name="_token" value="{{ csrf_token() }}" />
<label>On <input type="radio" value="1" name="streamer" id="streamer-on"></label>
<label>Off <input type="radio" value="0" name="streamer" checked></label>
</form>

@endif

@if ($user->streamer && !$user->streaming_username)

<form id="streamer-profile">
<input type="hidden" name="_token" value="{{ csrf_token() }}" />
<label>Twitch username <input type="text" name="streaming_username" value="{{ $user->twitch_username }}"></label>
<label>League summoner name <input type="text" name="summoner"></label>
<input type="button" value="search" id="summoner-search">
<input type="submit" value="update">
</form>

@endif

@endsection