@extends('app')

@section('content')

pledges.create

@if ($streamer)
	Streamer: {{ $streamer->id }}
@endif

@endsection